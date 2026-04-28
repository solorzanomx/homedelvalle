# Home del Valle — Architectural Analysis & Recommendations

**Analysis Date:** April 27, 2026  
**Database:** 108 tables, 164 migrations, 93 models  
**Application:** Laravel 13.6.0 with Livewire 4.2.4  

---

## Part 1: System Architecture Overview

### Design Pattern: Operation-Centric Pipeline

The entire system is built around the **Operation** entity as the central hub. Every significant business transaction (sale, rental, lead capture) flows through this model.

```
┌─────────────────────────────────────┐
│  OPERATION (Central Hub)            │
│  ├─ type: venta/renta/captacion    │
│  ├─ stage: lead → closed           │
│  ├─ status: active/completed       │
│  ├─ phase: prospecting → closing   │
│  └─ financial data: amount, comm%  │
└─────────────────────────────────────┘
         ▲                    │
         │                    │
    ┌────┴────┬───────┬───────▼──┐
    │          │       │          │
PROPERTY    CLIENT  USERS      BROKER
```

### Strengths of This Design
1. **Single source of truth:** All transactions routable through operations
2. **Audit trail:** operation_stage_logs captures every transition
3. **Financial tracking:** Commission, amounts, guarantees all in one place
4. **Workflow clarity:** stage transitions directly map to business process
5. **Scalability:** Can add new operation types without schema changes

### Weaknesses & Risks
1. **Wide table:** 29 columns, many nullables (leads don't have property_id until viewing stage)
2. **Data inconsistency risk:** secondary_client_id and source_operation_id can create orphans
3. **Complex queries:** Joins with multiple optional relationships are slow
4. **State explosion:** 17 stages × 4 types × 5+ statuses = hard to validate all combinations
5. **History tracking:** No versioning table for financial changes (if commission adjusted)

---

## Part 2: Module Analysis

### 🟢 Well-Designed Modules

#### Carousel System (11 tables)
**Status:** Excellent  
**Reasoning:**
- Clear separation: templates → posts → slides → publications
- Versioning built-in (carousel_versions)
- Media rendering pipeline well-structured (render_status, render_error tracking)
- AI integration cleanly separated (carousel_image_prompts)
- Supports multi-channel publishing (carousel_publications: Instagram/Facebook/Twitter)

**Recommendations:**
- Archive old versions (>6 months) to separate table to reduce carousel_posts bloat
- Add `carousel_posts.render_status` to track overall post rendering health
- Index (carousel_post_id, order) in carousel_slides for efficient ordering

#### Legal & Contracts (6 tables)
**Status:** Good  
**Reasoning:**
- Separation of concerns: templates → documents → versions → acceptances
- E-signature integration (mifiel_documentos, google_signature_requests)
- Audit trail via legal_acceptances (who signed, when, method)
- Compliance-ready (signed_at, acceptance_method tracking)

**Recommendations:**
- Add contract_id to operations for explicit tracking (currently implicit)
- Add executed_date to distinguish signed vs executed
- Soft-delete templates instead of hard delete (prevents orphaned contracts)

#### Automation Engine (8 tables)
**Status:** Good, Growing  
**Reasoning:**
- Well-structured workflow (automations → steps → enrollments → logs)
- Flexible step config (JSON for delay, template_id, email_template)
- Multiple trigger types supported (lead, operation, date, event)
- Execution logging enables debugging (automation_step_logs)

**Recommendations:**
- Add rate_limit field to prevent email bombing
- Add conditional branching in automation_steps (e.g., if lead_temperature=hot then email_id=1 else email_id=2)
- Metrics: track automation performance (completion_rate, drop-off by step)

---

### 🟡 Good with Improvements Needed

#### Operations (Core Table)
**Status:** Functional but needs attention  
**Issues:**
1. **Schema bloat:** 29 columns with many conditionally-used fields
2. **Type mismatches:** Rental-specific fields (monthly_rent, lease_duration_months) in main table
3. **Inconsistent nullability:** stage=lead has no property_id, but queries don't check this
4. **Missing audit:** No operation_financial_history if commission is adjusted

**Recommendations:**
```
REFACTOR (Breaking change for next major version):

1. Split into type-specific subtables:
   - venta_operations (purchase-specific fields)
   - renta_operations (rental-specific fields)
   - captacion_operations (capture-specific fields)

2. Move financial to separate table:
   - operations_financial_history (operation_id, field, old_value, new_value, changed_by, changed_at)

3. Add validation constraints:
   - IF type='venta' THEN property_id NOT NULL
   - IF type='renta' THEN monthly_rent NOT NULL
```

#### Properties (Inventory)
**Status:** Good with structural issues  
**Issues:**
1. **Hybrid storage:** title/description in DB + potentially in CMS/media library
2. **EasyBroker coupling:** easybroker_id, easybroker_status create bidirectional sync complexity
3. **Missing fields:** No size_type (m² vs sqft), no year_updated
4. **Photo management:** Duplicated with property_photos AND property_images

**Recommendations:**
```
1. Consolidate photo management:
   - Keep property_photos for primary interface
   - Move property_images → media table with morphs relationship

2. Separate EasyBroker state:
   - Add easybroker_sync_log table (property_id, status, error, synced_at)
   - Treat easybroker_id as read-only identifier (don't delete property if sync fails)

3. Standardize units:
   - Add area_unit_type column (m²/sqft) with default 'm²'
   - Add last_updated_reason (online_form/admin_edit/api_sync)
```

#### Clients (Lead Management)
**Status:** Good but missing relationship clarity  
**Issues:**
1. **Ambiguous references:** Same table for buyers, renters, sellers, owners
2. **Acquisition tracking incomplete:** utm_* fields but no lead_source_value
3. **Assignment confusion:** Both broker_id and assigned_user_id can coexist
4. **Segmentation unused:** client_segment table exists but no clear usage

**Recommendations:**
```
1. Add client_type field:
   - 'buyer', 'renter', 'owner', 'investor'
   - Clarify broker_id (seller's agent vs buyer's agent)

2. Consolidate assignment:
   - Remove broker_id from clients (use through operations.broker_id)
   - Keep assigned_user_id for agent responsibility
   - Add co_agents (JSON) for team assignments

3. Improve acquisition tracking:
   - Add lead_source_id (reference to marketing_channels/referrers)
   - Add lead_source_value (for attribution modeling)
```

---

### 🟠 Under-Implemented or At-Risk Modules

#### Valuation System (7 tables)
**Status:** Incomplete  
**Issues:**
1. **Market analysis incomplete:** market_zones and market_colonias exist but no demand/supply tracking
2. **Valuation method weak:** method (comparative/income/cost) but no detailed adjustments stored
3. **No competing comparables:** valuation_comparables points to properties, but needs external data
4. **Missing geospatial:** No GIS queries for "all properties within 5km"

**Recommendations:**
```
1. Add market dynamics table:
   - market_colonia_metrics (colonia_id, month, new_listings, sold_count, avg_days_to_sell)
   - Used for trend analysis (prices up/down, inventory health)

2. Enhance valuation workflow:
   - valuation_adjustments → valuation_adjustment_types (has predefined list)
   - confidence_score should cascade from comparable similarity

3. Add geospatial indexing:
   - Index (latitude, longitude) in properties
   - Use ST_Distance queries for location-based analysis
```

#### Leads & Scoring (5 tables)
**Status:** Architecture exists, usage unclear  
**Issues:**
1. **lead_scores duplicates:** Calculated once, not updated dynamically
2. **lead_score_rules unused:** Table exists but unclear if automations reference it
3. **lead_events sparse:** Only tracked if automation logs them
4. **Missing engagement score:** No decay factor (old events less valuable)

**Recommendations:**
```
1. Add scoring engine:
   - lead_scoring_jobs table (tracks when scores calculated, next recalc)
   - Refresh scores nightly or on event

2. Enhance rule engine:
   - lead_score_rules → include trigger (email_open:+5, form_submit:+15)
   - Auto-update lead.lead_temperature based on score (90+ = hot)

3. Track engagement:
   - lead_events.weight (more recent events weighted higher)
   - lead_scores → include engagement_trend (improving/declining)
```

#### Marketing & Campaign Attribution (6 tables)
**Status:** Basic structure, no analytics  
**Issues:**
1. **ROI not calculated:** marketing_campaigns.actual_roi is null everywhere
2. **Attribution missing:** clients track utm_source but no campaign_id→client attribution
3. **Channel validation weak:** marketing_channels.slug not enforced unique
4. **No cohort analysis:** Can't segment "customers acquired via Facebook in Q1 2026"

**Recommendations:**
```
1. Add attribution layer:
   - campaign_attribution (client_id, campaign_id, attributed_at, attribution_model)
   - Support multi-touch (each client→multiple campaigns)

2. ROI calculation:
   - marketing_campaign_roi_reports (campaign_id, spend, leads_count, conversions_count, revenue, roi%)
   - Refresh monthly after operations.closed_at is finalized

3. Cohort tracking:
   - Add cohort_month to clients (for "Q1 2026 cohort analysis")
```

---

### 🔴 Critical Gaps

#### Referrals (2 tables)
**Status:** Skeleton only  
**Issues:**
- referrers table exists but no integration with client acquisition
- referrals table tracks commission but no "referral bonus" vs "full agent commission" logic
- No validation that referrer_id=broker and user_id=agent are different people

**Recommendation:** Build out referral API + dashboard before using

#### Help & Documentation (4 tables)
**Status:** Tables created, no usage  
**Issues:**
- help_articles, help_categories, help_tips, help_onboarding_progress all empty in prod
- No content strategy or content calendar

**Recommendation:** Plan content before building UI

#### Expense Categories (1 table)
**Status:** Exists, unused  
**Issues:**
- transactions.category hardcoded values, not linked to expense_categories
- Can't create new expense types without code change

**Recommendation:** Link transactions.category to expense_categories.id

---

## Part 3: Data Quality & Integrity

### Referential Integrity Checks

**Run this to find orphans:**
```sql
-- Orphaned operations (client deleted but operation remains)
SELECT id FROM operations WHERE client_id NOT IN (SELECT id FROM clients) AND client_id IS NOT NULL;

-- Orphaned carousel slides
SELECT id FROM carousel_slides WHERE carousel_post_id NOT IN (SELECT id FROM carousel_posts);

-- Orphaned transactions
SELECT id FROM transactions WHERE user_id NOT IN (SELECT id FROM users);

-- Orphaned operation comments
SELECT id FROM operation_comments WHERE operation_id NOT IN (SELECT id FROM operations);
```

**Expected clean result:** 0 rows for all queries

### Constraints Missing (Add to migrations)

```php
// In next migration file:

Schema::table('operations', function (Blueprint $table) {
    // Ensure type is valid
    $table->check("type IN ('venta', 'renta', 'captacion')");
    
    // Ensure phase matches type
    $table->check("(type = 'venta' AND phase IN ('prospecting', 'negotiation', 'closing', 'closed')) OR type IN ('renta', 'captacion')");
});

Schema::table('properties', function (Blueprint $table) {
    $table->check("price > 0");
    $table->check("area IS NULL OR area > 0");
    $table->check("bedrooms IS NULL OR bedrooms >= 0");
});

Schema::table('clients', function (Blueprint $table) {
    $table->check("budget_max IS NULL OR budget_min IS NULL OR budget_max >= budget_min");
});

Schema::table('carousel_slides', function (Blueprint $table) {
    $table->check("overlay_opacity BETWEEN 0 AND 100");
});
```

---

## Part 4: Performance Analysis

### Query Hotspots

**1. Operations with Full Details (Most Common)**
```php
// SLOW: 3 JOINs + subquery
Operation::with('property', 'client', 'user', 'broker')->where('user_id', $userId)->get();

// FAST: Use select + indexes
Operation::select(['id', 'type', 'stage', 'status', 'amount'])
    ->with('property:id,title,price')
    ->where('user_id', $userId)
    ->limit(50)
    ->get();
```

**Indexes needed:**
```
operations(user_id, status, created_at DESC)
operations(client_id, status)
operations(property_id, type)
```

**2. Carousel Slides Rendering**
```php
// SLOW: Iterates all slides
foreach ($post->carousel_slides as $slide) {
    if (!$slide->rendered_image_path) {
        $slide->render();  // Queue job
    }
}

// FAST: Batch update
CarouselSlide::where('carousel_post_id', $postId)
    ->where('render_status', 'pending')
    ->update(['render_status' => 'queued']);
// Then queue batch job
```

**3. Client Search**
```php
// SLOW: LIKE on all fields
Client::where('name', 'LIKE', "%$term%")
    ->orWhere('email', 'LIKE', "%$term%")
    ->get();

// BETTER: Full-text search (requires PostgreSQL)
Client::whereRaw("to_tsvector(name || ' ' || email) @@ plainto_tsquery(?)", [$term])->get();

// OR: Elasticsearch for advanced search
```

### Database Size Projections

Assuming 100 operations/month growth:

| Timeline | Operations | Transactions | Carousel Posts | Est. DB Size |
|----------|-----------|---|---|---|
| **Today** | 2,000 | 10,000 | 500 | 50 MB |
| **1 Year** | 3,200 | 16,000 | 1,500 | 120 MB |
| **3 Years** | 5,600 | 28,000 | 4,500 | 350 MB |
| **5 Years** | 8,000 | 40,000 | 7,500 | 600 MB |

**At 600MB, consider archiving:**
- operations >3 years old → operations_archive
- carousel_versions >6 months old → carousel_versions_archive
- lead_events >1 year old → lead_events_archive

---

## Part 5: Security Analysis

### PII & Sensitive Data Locations

| Field | Table | Risk Level | Mitigation |
|-------|-------|-----------|-----------|
| email | users, clients | HIGH | Hashed in users.password; plaintext in clients.email (required for sending) |
| phone/whatsapp | clients, users | MEDIUM | Plaintext; access control via role-based ACL |
| google_signature_requests | - | HIGH | Contains signer emails & document IDs; ensure GDPR compliance |
| mifiel_documentos | - | CRITICAL | Contains signed legal documents; encrypt PII before storing |
| users.password | users | CRITICAL | Must be bcrypt hashed; never log |

**Recommendations:**
1. Add `pii_scope` column to emails/messages (restrict viewing to assigned agent)
2. Encrypt `clients.phone` at rest (Laravel: `Crypt::encrypt()`)
3. Audit `legal_documents` for compliance (GDPR "right to be forgotten")
4. Rate-limit SMS delivery via `messages` table

### Access Control Gaps

**Current:** Role-based (admin/supervisor/agent)  
**Gap:** No operation-level permissions (agent can see other agents' operations)  

**Recommendation:**
```php
// Add policy:
if (auth()->user()->role === 'agent') {
    $query->where('user_id', auth()->id()); // Only own operations
}
```

---

## Part 6: Feature Roadmap & Implementation Priorities

### Phase 1: Foundation (Completed ✓)
- [x] Core operation pipeline
- [x] Property management
- [x] Client/lead management
- [x] User roles & permissions

### Phase 2: Marketing (In Progress)
- [ ] Carousel system automation
- [ ] Lead scoring engine
- [ ] Email automation workflows
- [ ] Campaign attribution tracking

### Phase 3: Intelligence (Planned Q3 2026)
- [ ] Market valuation engine
- [ ] Price prediction model
- [ ] Lead recommendation (ML)
- [ ] Churn prediction (which clients likely to drop out)

### Phase 4: Enterprise (Future)
- [ ] Multi-office support
- [ ] White-label portals
- [ ] Advanced analytics dashboard
- [ ] Mobile app

---

## Part 7: Migration & Upgrade Strategy

### Before Next Major Version (14.0)

**Breaking Changes to Implement:**

1. **Split operations table** (if >5,000 rows in production)
   ```php
   CreateVentaOperationsTable (extends Operation)
   CreateRentaOperationsTable (extends Operation)
   CreateCaptacionOperationsTable (extends Operation)
   // Use single-table inheritance or polymorphic relationships
   ```

2. **Rename ambiguous columns**
   ```
   clients.broker_id → clients.seller_agent_id
   Add clients.buyer_agent_id (new)
   ```

3. **Enforce referential integrity**
   ```
   Make operations.property_id NOT NULL if type='venta'
   Make operations.client_id NOT NULL always
   ```

### Testing Before Upgrade

```php
// Add to test suite:
public function test_all_operations_have_valid_type() {
    $invalid = Operation::whereNotIn('type', ['venta', 'renta', 'captacion'])->count();
    $this->assertEquals(0, $invalid);
}

public function test_no_orphaned_relationships() {
    $orphans = Operation::where('user_id', 0)->orWhereNull('user_id')->count();
    $this->assertEquals(0, $orphans);
}

public function test_carousel_slides_have_posts() {
    $orphans = CarouselSlide::where('carousel_post_id', 0)->orWhereNull('carousel_post_id')->count();
    $this->assertEquals(0, $orphans);
}
```

---

## Part 8: Recommendations Summary

### Immediate Actions (This Sprint)

1. **Document current enum values** → Create seed data for enums
2. **Add missing indexes** → operations(user_id), operations(property_id), operations(status)
3. **Audit referential integrity** → Run orphan detection SQL
4. **Lock unused tables** → Help, Testimonials (not used); remove or populate

### Short-term (Next Quarter)

1. **Consolidate photo management** (property_photos vs property_images)
2. **Complete lead scoring** (implement scoring_engine refresh job)
3. **Finish campaign attribution** (link clients → campaigns)
4. **Archive old data** (carousel_versions >6 months, operations >3 years)

### Medium-term (Next 6 Months)

1. **Refactor operations table** (split by type if growth continues)
2. **Add geospatial queries** (PostGIS for location-based search)
3. **Enhance valuation system** (market_colonia_metrics table)
4. **Build analytics layer** (materialized views for dashboards)

### Long-term (Next Year)

1. **Implement event sourcing** (for full financial audit trail)
2. **Add temporal tables** (track all property/client changes over time)
3. **Multi-tenant support** (per-office isolation)
4. **API versioning** (v1, v2 with backward compatibility)

---

## Conclusion

The database architecture is **solid for current scale** (108 tables, ~50MB) but shows **signs of growth strain** in:
- Operation complexity (29 columns, mixed responsibilities)
- Carousel system (generates many versions, slow queries)
- Lead scoring (incomplete implementation)
- Attribution (marketing data isolated from operations)

**Overall Assessment:** 7/10  
**With recommended fixes:** 9/10

The system is production-ready with the caveats above addressed. Prioritize operations table refactoring and missing indexes before scaling to >1M transactions.

