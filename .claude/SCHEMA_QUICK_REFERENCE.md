# Database Schema — Quick Reference Guide

## The Five Core Tables (80% of logic)

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| **operations** | Pipeline (venta/renta/captacion) | type, stage, status, property_id, client_id, user_id, amount, commission_percentage |
| **properties** | Real estate inventory | price, bedrooms, bathrooms, area, status, operation_type, easybroker_id |
| **clients** | Leads & buyers & renters | email, phone, budget_min/max, property_type, lead_temperature, broker_id |
| **users** | Staff & agents | email, role (supervisor/agent/admin), can_read/edit/delete, is_active |
| **carousel_posts** | Instagram content | title, type, caption_long, status, template_id, published_at |

---

## Operation Pipeline Stages (17 states)

```
VENTA flow:        RENTA flow:           CAPTACION flow:
lead               lead                  inquiry
↓                  ↓                      ↓
viewing            viewing               captured
↓                  ↓                      ↓
offer              offer                 evaluation
↓                  ↓                      (terminal)
signed             signed
↓                  ↓
funded             funded
↓                  ↓
closed             closed
```

**Transitions tracked in:** `operation_stage_logs`

---

## How to Query Common Patterns

### Find all active operations by agent
```php
Operation::where('user_id', $userId)
    ->where('status', 'active')
    ->with('property', 'client', 'broker')
    ->get();
```

### Property with all upcoming operations
```php
Property::with([
    'operations' => fn($q) => $q->where('status', 'active')->orderBy('expected_close_date')
])->find($propertyId);
```

### Client's transaction history
```php
Transaction::where('property_id', $propertyId)
    ->orWhere('deal_id', DB::table('deals')->select('id')->where('client_id', $clientId))
    ->orderBy('date', 'desc')
    ->get();
```

### Carousel posts ready to publish
```php
CarouselPost::where('status', 'approved')
    ->where('published_at', null)
    ->with('carousel_slides', 'carousel_template')
    ->get();
```

### Market analysis for a zone
```php
MarketZone::find($zoneId)->colonias()
    ->with(['price_snapshots' => fn($q) => $q->latest()])
    ->get();
```

---

## Enum Values Cheat Sheet

### operation.type
`'venta'` | `'renta'` | `'captacion'`

### operation.stage
- **1-3:** Prospecting (lead, viewing, offer)
- **4-5:** Negotiation (signed, funded)
- **6:** Closed
- **+11 more** (see SCHEMA_QUICK_REFERENCE.md for full list)

### operation.status
`'active'` | `'completed'` | `'cancelled'` | `'on_hold'`

### operation.phase
`'prospecting'` | `'negotiation'` | `'closing'` | `'closed'`

### property.operation_type
`'venta'` | `'renta'` | `'venta_anticipada'`

### property.property_type
`'house'` | `'apartment'` | `'land'` | `'commercial'` | `'development'`

### property.status
`'active'` | `'sold'` | `'rented'` | `'inactive'`

### client.lead_temperature
`'hot'` | `'warm'` | `'cold'`

### user.role
`'admin'` | `'supervisor'` | `'agent'` | `'client'`

### carousel_post.type
`'property'` | `'market'` | `'testimonial'` | `'tips'`

### carousel_post.status
`'draft'` | `'pending'` | `'approved'` | `'published'`

### transaction.type
`'income'` | `'expense'` | `'commission'` | `'cost'`

### automation.trigger_type
`'lead'` | `'operation'` | `'date'` | `'event'`

---

## Critical Paths Through Data

### Viewing a Sale Operation
```
operation (type=venta, stage=viewing)
  ├→ property (the house being viewed)
  ├→ client (buyer)
  ├→ user (agent)
  ├→ broker (seller's agent)
  ├→ operation_comments (notes)
  ├→ operation_checklist_items (inspection, title review, etc.)
  └→ operation_stage_logs (audit trail)
```

### Rental Process
```
operation (type=renta, monthly_rent, lease_duration_months)
  ├→ rental_processes (lessee, lessor, contract dates)
  ├→ rental_stage_logs (renter screening → move-in → active → move-out)
  ├→ contracts (lease agreement)
  └→ transactions (monthly rent deposits, fees)
```

### Carousel Publishing
```
carousel_post (status=pending approval)
  ├→ carousel_slides (individual slide designs)
  │   ├→ rendered_image_path (generated image)
  │   └→ carousel_image_prompts (AI generation history)
  ├→ carousel_versions (iteration history)
  ├→ carousel_template (design template used)
  └→ carousel_publications (pushed to Instagram/Facebook)
```

### Lead Automation
```
client (new lead captured)
  ├→ lead_scores (automatic scoring based on rules)
  ├→ lead_events (email opened, link clicked, etc.)
  ├→ automation_enrollments (client enrolled in "hot lead" workflow)
  ├→ automation_steps (send welcome email → wait 3 days → send property suggestions → wait 1 week → send final offer)
  └→ automation_step_logs (execution record: "email sent at 2:30pm", "click recorded at 3:45pm")
```

---

## Large Tables to Watch (Performance)

| Table | Est. Rows | Growth Rate | Concern |
|-------|-----------|------------|---------|
| carousel_slides | 5,000 | +100/week | Rendering bottleneck |
| carousel_posts | 500 | +20/week | Version/publication duplication |
| operations | 2,000+ | +50/month | Query complexity |
| transactions | 10,000+ | +200/month | Report generation slowness |
| carousel_versions | 2,000+ | +50/week | Storage bloat (redundant content) |
| operations comments | 5,000+ | +100/month | Large text field (notes) |

**Recommendation:** Archive carousel_posts + versions >6 months old to separate "archive" table.

---

## Critical Constraints

| Constraint | Impact | Action |
|-----------|--------|--------|
| operations.user_id CASCADE delete | Deleting agent deletes all their operations | Never delete users; use is_active=false |
| properties.easybroker_id UNIQUE | Only one property per EasyBroker listing | Validate before syncing |
| clients.email UNIQUE per broker | Email can repeat across brokers | Consider app-wide uniqueness |
| carousel_templates.blade_view | Must exist as file | Validate on save; test template rendering |
| contracts.template_id nullable | Templates can be deleted | Soft-delete contract templates instead |

---

## Relationships to Avoid (Performance Gotchas)

🚫 **Don't do:** `Operation::with('property.carousel_posts.carousel_slides.carousel_images')`  
✅ **Instead:** Query carousel_posts separately or limit with chunks

🚫 **Don't do:** `Client::with('operations', 'lead_scores', 'lead_events', 'interactions')`  
✅ **Instead:** Load relationships conditionally based on view

🚫 **Don't do:** `CarouselPost::with('carousel_versions.carousel_slides')`  
✅ **Instead:** Load versions separately; use eager loading with constraints

---

## Data Integrity Rules

1. **Operations must have a type:** Enforce in validation + DB constraint
2. **Properties must have currency:** Always match operation currency
3. **Client emails must be unique:** Within broker context
4. **Carousel slides require carousel_post:** Never orphan slides
5. **Automation enrollments auto-cleanup:** When automation is deleted
6. **Lease end_date > start_date:** Validate before saving
7. **Commission percentage must be 0-100:** DB constraint + app validation
8. **Market snapshots immutable:** Set created_at, no updates to historical data

---

## Migration Strategy (if adding new tables)

1. Create table with all required columns
2. Add foreign keys separately (easier rollback)
3. Create indexes for frequently filtered columns
4. Add tests for relationship loading
5. Run analysis query to confirm zero orphaned rows
6. Update model relationships in PHP
7. Add factory/seeder for testing

Example:
```php
Schema::create('new_table', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

Schema::table('new_table', function (Blueprint $table) {
    $table->foreignId('operation_id')->constrained()->onDelete('cascade');
});
```

---

## Monitoring Queries

```sql
-- Table sizes (SQLite)
SELECT name, page_count * 4096 / 1024 / 1024 as "MB"
FROM pragma_page_count(), sqlite_master
WHERE type='table'
ORDER BY page_count DESC;

-- Orphaned foreign keys
SELECT * FROM operations WHERE user_id NOT IN (SELECT id FROM users);

-- Slow operations (missing indexes)
SELECT user_id, COUNT(*) as op_count
FROM operations
WHERE created_at > datetime('now', '-30 days')
GROUP BY user_id
ORDER BY op_count DESC;

-- Carousel slide rendering failures
SELECT COUNT(*), render_status
FROM carousel_slides
GROUP BY render_status;

-- Automation enrollment status
SELECT automation_id, status, COUNT(*) as count
FROM automation_enrollments
GROUP BY automation_id, status;
```

---

## Add-On: Entity Relationship Diagram (Compact)

```
┌─────────────┐         ┌──────────────┐
│   USERS     │ ◄─────► │  OPERATIONS  │
│             │         │              │
│ id          │         │ id           │
│ email       │         │ user_id ───► │
│ role        │         │ property_id  │
└─────────────┘         │ client_id    │
       ▲                │ broker_id    │
       │                │ amount       │
       │                │ commission   │
       │                └──────────────┘
       │                      ▲
    ┌──┴────────┬──────┐      │
    │           │      │      │
┌───▼──┐ ┌──────▼─┐┌──▼────┐ │
│CLIENTS│ │BROKERS││PROPERTIES│
│       │ │       ││        │
│ id    │ │ id    ││ id     │
│ email │ │ name  ││ price  │
│ budget│ └───────┘└────────┘
└───────┘

┌──────────────────────┐
│ CAROUSEL_POSTS       │
│                      │
│ id                   │
│ template_id ────────┐│
│ status               ││
│ published_at         ││
└──────────────────────┘│
        │               │
        │      ┌────────▼─────┐
        │      │ CAROUSEL_    │
        │      │ TEMPLATES    │
        │      │              │
        │      │ blade_view   │
        │      └──────────────┘
        │
        ├─► CAROUSEL_SLIDES
        ├─► CAROUSEL_VERSIONS
        └─► CAROUSEL_PUBLICATIONS

┌──────────────────────┐
│ AUTOMATIONS          │
│                      │
│ id                   │
│ trigger_type         │
│ is_active            │
└──────────────────────┘
        │
        ├─► AUTOMATION_STEPS
        ├─► AUTOMATION_RULES
        └─► AUTOMATION_ENROLLMENTS ────► CLIENTS
```

---

## Quick Links in Codebase

| Location | Purpose |
|----------|---------|
| `app/Models/` | All 93 model definitions |
| `database/migrations/` | 164 migration files (ordered by date) |
| `database/seeders/` | Test data generation |
| `database/factories/` | Model factories for testing |
| `routes/` | All route definitions (357 routes) |
| `app/Http/Controllers/` | 98 controllers |

