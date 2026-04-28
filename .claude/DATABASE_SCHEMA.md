# Home del Valle — Complete Database Schema Documentation

**Generated:** April 27, 2026  
**Database:** SQLite (164 migrations, 108 tables)  
**Laravel Version:** 13.6.0  

---

## Executive Summary

The database architecture is organized into **18 logical modules** containing **108 tables** managed through **164 migrations**. The system is built around a **central Operation pipeline** that orchestrates all transactions, property management, and client interactions.

### Key Statistics
- **Total Tables:** 108
- **Total Migrations:** 164
- **Core Models:** 93 (from prior analysis)
- **Module Categories:** 18
- **Database Engine:** SQLite (development) / PostgreSQL (production)

---

## Module 1: Core Foundation (3 tables)

### Users & Authentication
- **users** (33 columns)
  - Core user account with email, password, roles
  - Fields: name, email, password, role (supervisor/agent/admin), is_active, avatar_path
  - Permissions: can_read, can_edit, can_delete (boolean flags)
  - Additional: phone, whatsapp, address, timezone, language, website_order
  - Relationships: has_many(operations), has_many(clients)

### Session Management
- **password_reset_tokens**
  - Email-based password reset mechanism
  - Fields: email, token, created_at

- **sessions**
  - PHP session storage
  - Fields: user_id, ip_address, user_agent, payload, last_activity

---

## Module 2: Property Management (10 tables)

### Primary Property Tables
- **properties** (38 columns)
  - Central property listing with full details
  - Location: city, colony, address, zipcode, latitude, longitude
  - Physical: bedrooms, bathrooms, half_bathrooms, parking, area, lot_area, floors, year_built
  - Pricing: price, maintenance_fee, currency, is_featured
  - Status: status (active/sold/rented), property_type (house/apt/land/commercial), operation_type (venta/renta)
  - Integration: easybroker_id, easybroker_status, easybroker_published_at
  - Media: photo (primary), youtube_url
  - Metadata: description, amenities, furnished
  - Relationships: belongs_to(broker), belongs_to(client), has_many(operations), has_many(property_photos), has_many(property_qr_codes)

- **property_photos** (6 columns)
  - Individual property photos with order
  - Fields: property_id, photo_path, display_order, is_primary, created_at, updated_at
  - Constraint: up to 50 photos per property

- **property_images** (6 columns)
  - Alternative image storage with media library integration
  - Fields: property_id, image_path, caption, order, created_at, updated_at

- **property_qr_codes** (5 columns)
  - QR codes for property sharing/marketing
  - Fields: property_id, qr_code, qr_url, created_at, updated_at

### EasyBroker Integration
- **easybroker_settings** (6 columns)
  - API configuration for EasyBroker sync
  - Fields: api_key, is_active, last_sync_at, sync_frequency (minutes), created_at, updated_at

---

## Module 3: Client & Lead Management (9 tables)

### Primary Client Tables
- **clients** (27 columns)
  - Full buyer/renter profile with preferences and acquisition tracking
  - Contact: name, email, phone, whatsapp, address, city
  - Preferences: property_type, budget_min/max, interest_types, zone_of_interest
  - Acquisition: marketing_channel_id, marketing_campaign_id, acquisition_cost, utm_source/medium/campaign
  - Status: lead_temperature (hot/warm/cold), priority, is_active
  - Assignment: broker_id, user_id (owner), assigned_user_id
  - Metadata: photo, initial_notes

- **client_emails** (5 columns)
  - Historical email correspondence
  - Fields: client_id, email, verified_at, created_at, updated_at

- **client_segment** (4 columns)
  - Segmentation for marketing campaigns
  - Fields: client_id, segment_id, created_at, updated_at

### Lead Scoring & Events
- **lead_scores** (6 columns)
  - Calculated lead quality score
  - Fields: client_id, score (0-100), rule_source, calculated_at, created_at, updated_at

- **lead_score_rules** (6 columns)
  - Rules engine for automatic lead scoring
  - Fields: name, criteria (JSON), score_value, is_active, created_at, updated_at

- **lead_events** (7 columns)
  - Lead activity timeline
  - Fields: client_id, event_type (email_open/click/form_submit), metadata (JSON), created_at, updated_at

### Contact & Form Management
- **contact_submissions** (7 columns)
  - Website contact form submissions
  - Fields: name, email, phone, subject, message, source_url, status, ip_address, created_at

- **form_submissions** (8 columns)
  - Dynamic form responses with type tracking
  - Fields: form_id, submission_data (JSON), submitter_email, submitter_name, ip_address, is_spam, created_at, updated_at

- **forms** (6 columns)
  - Form builder templates
  - Fields: name, slug, description, fields_config (JSON), is_active, created_at, updated_at

---

## Module 4: Operation Pipeline (8 tables)

### Central Operations
- **operations** (29 columns) — **CORE ENTITY**
  - Every transaction flows through this table
  - Type: type (venta/renta/captacion), phase (prospecting/negotiation/closing/closed), status (active/completed/cancelled)
  - Stage: stage (lead/viewing/offer/signed/funded/closed) — 17 possible stages
  - Participants: property_id, client_id, secondary_client_id, broker_id, user_id (primary agent)
  - Financial: amount, commission_amount, commission_percentage, monthly_rent, deposit_amount, guarantee_type
  - Dates: lease_start_date, lease_end_date, expected_close_date, closed_at, completed_at, cancelled_at
  - Lease: lease_duration_months, currency
  - Metadata: notes, target_type (optional), source_operation_id (for linked operations)
  - Relationships: belongs_to(property), belongs_to(client), belongs_to(user), has_many(operation_comments), has_many(operation_stage_logs), has_many(tasks)

### Operation Tracking
- **operation_stage_logs** (6 columns)
  - Audit trail of stage transitions
  - Fields: operation_id, from_stage, to_stage, reason, user_id, created_at

- **operation_comments** (7 columns)
  - Internal notes and collaboration
  - Fields: operation_id, user_id, content, mention_user_ids (JSON), created_at, updated_at

- **operation_checklist_items** (8 columns)
  - Stage-specific task checklist
  - Fields: operation_id, stage, item_name, description, is_completed, completed_by, completed_at, created_at

### Stage Management
- **stage_checklist_templates** (6 columns)
  - Reusable checklist templates per stage
  - Fields: stage, name, items (JSON: [{ name, description, required }]), created_at, updated_at

---

## Module 5: Deals & Transactions (4 tables)

### Deals (Oportunidades)
- **deals** (8 columns)
  - High-level opportunity tracking
  - Fields: name, status, property_id, client_id, expected_value, expected_close_date, user_id, created_at

### Financial Transactions
- **transactions** (16 columns)
  - All financial movements with categorization
  - Type: type (income/expense/commission/cost), category (commission/marketing/legal/inspection)
  - Amount: amount, currency, date, payment_method (cash/transfer/check)
  - Links: deal_id, property_id, broker_id, user_id
  - Metadata: description, reference (tracking), notes
  - Relationships: belongs_to(user), belongs_to(property), belongs_to(deal)

- **commissions** (7 columns)
  - Commission tracking and distribution
  - Fields: operation_id, broker_id, user_id, amount, percentage, status (pending/paid), paid_at, created_at

### Expense Management
- **expense_categories** (4 columns)
  - Chart of accounts for expense organization
  - Fields: name, description, parent_id (for hierarchy), created_at

---

## Module 6: Rental Management (2 tables)

- **rental_processes** (11 columns)
  - Complete rental workflow tracking
  - Lessee: lessee_id (client), lessor_id (property owner)
  - Contract: contract_start, contract_end, monthly_rent, deposit_amount, guarantee_type
  - Status: status (pending/active/completed/terminated), created_at, updated_at

- **rental_stage_logs** (6 columns)
  - Rental stage transitions (similar to operations)
  - Fields: rental_process_id, from_stage, to_stage, reason, user_id, created_at

---

## Module 7: Marketing & Campaigns (6 tables)

- **marketing_channels** (6 columns)
  - Lead source tracking (Facebook, Google, Website, Referral, etc.)
  - Fields: name, slug, description, is_active, sort_order, created_at

- **marketing_campaigns** (9 columns)
  - Campaign management and ROI tracking
  - Fields: name, channel_id, budget, start_date, end_date, status, roi_target, actual_roi, created_at

- **newsletter_subscribers** (6 columns)
  - Email subscription list
  - Fields: email, name, status (subscribed/unsubscribed), subscribed_at, unsubscribed_at, created_at

- **newsletter_campaigns** (8 columns)
  - Email campaign sending
  - Fields: name, subject, content, recipient_count, sent_at, open_rate, click_rate, created_at

- **contact_channels** — (referenced in clients but not found as separate table)
  - Integrated in client model as marketing_channel_id

- **facebook_posts** (6 columns)
  - Social media posting tracking
  - Fields: carousel_post_id, facebook_post_id, facebook_url, published_at, engagement_count, created_at

---

## Module 8: Carousel & Social Media (11 tables)

### Carousel System (AI-powered Instagram carousel generation)
- **carousel_templates** (13 columns)
  - Design templates for carousels
  - Fields: name, slug, description, thumbnail_path, blade_view, canvas_size (1080x1350)
  - Config: default_vars (JSON), supported_types (text/property/client)
  - Status: is_active, sort_order, created_at, updated_at

- **carousel_posts** (19 columns)
  - Individual carousel campaigns
  - Type: type (property/market/testimonial/tips), source_type (property/comparables/client)
  - Content: title, caption_short, caption_long, hashtags, cta (call-to-action)
  - AI: ai_prompt_used (for regeneration), approved_version_id
  - Status: status (draft/pending/approved/published), user_id, approved_by, approved_at, published_at
  - Relationships: has_many(carousel_slides), has_many(carousel_versions), belongs_to(carousel_template)

- **carousel_slides** (19 columns)
  - Individual slides within a carousel
  - Order: carousel_post_id, order (sequence)
  - Content: type, headline, subheadline, body, cta_text
  - Visuals: background_image_path, secondary_image_path, overlay_color, overlay_opacity (0-100)
  - Rendering: rendered_image_path, render_status (pending/success/failed), render_error
  - Metadata: custom_data (JSON), is_locked, created_at, updated_at

- **carousel_versions** (10 columns)
  - Version history for iteration/A-B testing
  - Fields: carousel_post_id, version_number, content (JSON), created_by, created_at

- **carousel_publications** (6 columns)
  - Publishing history to different channels
  - Fields: carousel_post_id, platform (instagram/facebook/twitter), platform_post_id, published_at, created_at

- **carousel_assets** (5 columns)
  - Reusable media library
  - Fields: name, type (image/vector), path, usage_count, created_at

- **carousel_image_prompts** (9 columns)
  - AI image generation tracking for carousel slides
  - Fields: carousel_slide_id, prompt, provider (openai/midjourney), generated_image_url, status, tokens_used, created_at

- **carousel_topic_suggestions** (9 columns)
  - AI-generated topic recommendations
  - Fields: session_id, title, description, reasoning, suggested_keywords (JSON), relevance_score, status, converted_post_id

- **blog_topic_suggestions** (10 columns)
  - Blog post topic generation
  - Fields: session_id, title, description, reasoning, suggested_keywords (JSON), relevance_score, status, converted_post_id

---

## Module 9: Blog & Content (6 tables)

- **posts** (13 columns)
  - Blog articles
  - Fields: title, slug, content, excerpt, featured_image, published_at, author_id, is_published, view_count, created_at

- **post_categories** (4 columns)
  - Blog category organization
  - Fields: name, slug, description, created_at

- **post_tag** (3 columns)
  - Many-to-many relationship between posts and tags
  - Fields: post_id, tag_id

- **tags** (4 columns)
  - Tag library
  - Fields: name, slug, description, created_at

- **help_articles** (7 columns)
  - Knowledge base / FAQ content
  - Fields: title, slug, content, category_id, is_published, order, created_at

- **help_categories** (4 columns)
  - Help article organization
  - Fields: name, slug, description, created_at

---

## Module 10: Help & Onboarding (4 tables)

- **help_tips** (6 columns)
  - Contextual help tooltips
  - Fields: title, content, context_page, target_element, icon_type, created_at

- **help_onboarding_progress** (6 columns)
  - Track user onboarding completion
  - Fields: user_id, step_name, completed_at, skipped, created_at

---

## Module 11: Legal & Contracts (6 tables)

- **contracts** (12 columns)
  - Contract management system
  - Type: type (lease/sale/purchase/other), status (draft/signed/executed/terminated)
  - Parties: property_id, client_id, secondary_client_id, broker_id
  - Dates: start_date, end_date, signed_at, created_at
  - Fields: template_id, contract_number, created_at, updated_at

- **contract_templates** (6 columns)
  - Reusable contract templates with variable substitution
  - Fields: name, type, template_body (with {{ var }} syntax), is_active, created_at

- **legal_documents** (10 columns)
  - Document filing and tracking
  - Type: type (contract/deed/title/permit), status (pending/signed/notarized/filed)
  - Dates: created_date, notarized_date, filed_date, expiry_date
  - Storage: document_path, signed_by, created_at

- **legal_document_versions** (6 columns)
  - Version control for legal documents
  - Fields: legal_document_id, version_number, document_path, created_by, created_at

- **legal_acceptances** (6 columns)
  - Acceptance/signature tracking
  - Fields: legal_document_id, accepted_by (user_id), acceptance_method (email/esignature/in_person), accepted_at, created_at

- **mifiel_documentos** (8 columns)
  - Integration with Mifiel e-signature service
  - Fields: operation_id, document_id, document_type, status, signed_at, metadata (JSON), created_at

---

## Module 12: E-Signature & Compliance (2 tables)

- **google_signature_requests** (8 columns)
  - Google eSignature integration
  - Fields: user_email, document_path, signer_emails (JSON array), status (pending/signed/declined), signed_at, created_at

- **poliza_juridicas** (12 columns)
  - Legal insurance / warranty coverage
  - Type: type (property/title/liability), provider, policy_number
  - Dates: start_date, end_date, renewal_date, premium_amount, coverage_amount
  - Status: is_active, created_at, updated_at

---

## Module 13: Valuations & Market Analysis (7 tables)

### Valuation System
- **property_valuations** (10 columns)
  - Property valuation records
  - Fields: property_id, valuation_amount, valuation_date, method (comparative/income/cost), confidence_score (%), assessed_by, notes, created_at

- **valuation_comparables** (9 columns)
  - Comparable properties for valuation
  - Fields: valuation_id, comparable_property_id, adjustment_reason, adjustment_amount, adjusted_price, similarity_score, created_at

- **valuation_adjustments** (8 columns)
  - Fine-tuning valuations with adjustments
  - Fields: valuation_id, adjustment_name, adjustment_value, reason, created_by, created_at

- **valuation_leads** (7 columns)
  - Properties in valuation pipeline
  - Fields: property_id, requesting_party (owner/buyer/bank), valuation_status (pending/completed), estimated_value, created_at

### Market Analysis
- **market_zones** (6 columns)
  - Geographic zones for market analysis (Benito Juárez, Polanco, etc.)
  - Fields: name, slug, description, boundary_data (GeoJSON), is_active, created_at

- **market_colonias** (7 columns)
  - Neighborhood/colony data with market metrics
  - Fields: name, zone_id, market_index, avg_price_per_m2, avg_rental_per_m2, total_listings, created_at

- **market_price_snapshots** (6 columns)
  - Historical market price tracking
  - Fields: market_colonia_id, snapshot_date, avg_price, median_price, price_trend (%), created_at

---

## Module 14: Referrals & Partnerships (2 tables)

- **referrers** (8 columns)
  - Referral partners and sources
  - Fields: name, email, phone, type (agent/broker/company/other), status, commission_rate (%), notes, created_at

- **referrals** (10 columns)
  - Referral transaction tracking
  - Fields: referrer_id, operation_id, referred_client_id, commission_amount, status (pending/paid), paid_at, notes, created_at

---

## Module 15: Automation & Workflows (8 tables)

### Automation Engine
- **automations** (11 columns)
  - Workflow automation definitions
  - Fields: name, description, trigger_type (lead/operation/date), trigger_config (JSON)
  - Config: allow_reentry (bool), enrollment_count, created_by, is_active
  - Relationships: has_many(automation_steps), has_many(automation_enrollments)

- **automation_steps** (6 columns)
  - Individual workflow steps
  - Type: type (email/sms/task/webhook/delay), config (JSON: {delay: 3600, template_id: 1})
  - Fields: automation_id, position (order), created_at

- **automation_rules** (11 columns)
  - Conditional logic for automations
  - Fields: name, trigger (lead.temperature=hot), conditions (JSON), action (email/create_task)
  - Tracking: is_active, last_triggered_at, trigger_count, created_at

- **automation_enrollments** (8 columns)
  - Track which clients are in which automations
  - Fields: automation_id, client_id, current_step, status (active/paused/completed)
  - Dates: next_run_at, completed_at, created_at

- **automation_step_logs** (8 columns)
  - Execution history for debugging
  - Fields: enrollment_id, step_id, status (success/failed), result (JSON), error, executed_at, created_at

- **automation_logs** (7 columns)
  - Rule execution logging
  - Fields: automation_rule_id, trigger_data (JSON), action_result (JSON), status, error_message, created_at

- **messages** (7 columns)
  - Transactional message queue
  - Fields: type (email/sms/push), recipient, subject, body, status (pending/sent/failed), sent_at, created_at

- **interactions** (7 columns)
  - Track all client interactions
  - Fields: client_id, type (email/call/meeting/whatsapp), description, interaction_date, user_id, notes, created_at

---

## Module 16: AI & Integrations (4 tables)

- **ai_agent_configs** (11 columns)
  - LLM configuration for various use cases
  - Fields: key, label, description, provider (openai/anthropic/perplexity), model, max_tokens, temperature, is_active, created_at

- **blog_topic_suggestions** — (see Module 9)

- **carousel_topic_suggestions** — (see Module 8)

- **carousel_image_prompts** — (see Module 8)

---

## Module 17: Notifications & Communication (5 tables)

- **notifications** (8 columns)
  - In-app notification system
  - Fields: user_id, type (info/warning/error/success), title, message, action_url, is_read, created_at, updated_at

- **user_mail_settings** (6 columns)
  - Email preference management
  - Fields: user_id, newsletter (bool), digest (bool), notifications (bool), frequency, created_at

- **client_emails** — (see Module 3)

- **email_templates** (10 columns)
  - Email template library
  - Fields: name, slug, subject, body (with {{ var }} syntax), type (transactional/marketing), is_active, created_at

- **email_assets** (5 columns)
  - Email-specific media (logos, signatures)
  - Fields: name, type (logo/signature/banner), path, created_at

---

## Module 18: Site Configuration & Media (7 tables)

- **site_settings** (20 columns)
  - Global application configuration
  - Branding: company_name, logo_url, favicon_url, primary_color, secondary_color
  - Contact: email, phone, whatsapp, address, city
  - Social: facebook, instagram, linkedin, twitter
  - Integration: google_maps_key, recaptcha_key, seo_title, seo_description
  - Updated: updated_by, created_at, updated_at

- **email_settings** (8 columns)
  - SMTP & email configuration
  - Fields: mailer (smtp/mailgun/sendgrid), from_address, from_name, smtp_host, smtp_port, smtp_username, is_active

- **media** (10 columns)
  - Media library for all file uploads
  - Fields: name, original_filename, mime_type, size, disk (local/s3), path, disk, uuid, created_at

- **menus** (5 columns)
  - Navigation menu builder
  - Fields: name, slug, location (header/footer/sidebar), created_at

- **menu_items** (8 columns)
  - Individual menu items with nesting
  - Fields: menu_id, label, url, icon, order, parent_id (for hierarchy), created_at

- **pages** (10 columns)
  - Static pages (About, Contact, etc.)
  - Fields: title, slug, content, meta_title, meta_description, featured_image, is_published, order, created_at

- **testimonials** (8 columns)
  - Client testimonials for website
  - Fields: client_name, client_title, content, client_photo_url, rating (1-5), is_published, order, created_at

---

## Module 19: Job Queue & Caching (7 tables)

- **jobs** (6 columns)
  - Laravel queue system
  - Fields: queue, payload, attempts, reserved_at, available_at, created_at

- **job_batches** (7 columns)
  - Batch job processing
  - Fields: name, total_jobs, pending_jobs, failed_jobs, failed_job_ids (JSON), options (JSON), created_at

- **failed_jobs** (6 columns)
  - Failed job tracking for debugging
  - Fields: queue, payload, exception, failed_at, created_at

- **cache** (3 columns)
  - Cache key-value store
  - Fields: key, value, expiration

- **cache_locks** (3 columns)
  - Distributed cache locking
  - Fields: key, owner, expiration

- **migrations** (2 columns)
  - Migration tracking
  - Fields: migration, batch

- **password_reset_tokens** — (see Module 1)

---

## Data Relationships & Constraints

### Primary Entity Relationships

```
OPERATIONS (core)
├── belongs_to(properties)
├── belongs_to(clients) [primary]
├── belongs_to(clients) [secondary_client]
├── belongs_to(users)
├── belongs_to(brokers)
├── has_many(operation_comments)
├── has_many(operation_stage_logs)
├── has_many(tasks)
├── has_many(transactions)
├── has_many(contracts)
└── has_many(rental_processes)

PROPERTIES
├── has_many(operations)
├── has_many(property_photos)
├── has_many(property_images)
├── has_many(property_qr_codes)
├── belongs_to(brokers)
├── belongs_to(clients) [owner]
├── has_many(property_valuations)
└── has_many(carousel_posts) [source: property]

CLIENTS (leads/buyers/renters)
├── has_many(operations) [primary]
├── has_many(operations) [secondary]
├── has_many(client_emails)
├── has_many(lead_scores)
├── has_many(lead_events)
├── has_many(interactions)
├── has_many(referrals)
├── belongs_to(brokers)
├── belongs_to(users) [owner]
├── belongs_to(marketing_channels)
└── belongs_to(marketing_campaigns)

USERS
├── has_many(operations)
├── has_many(clients)
├── has_many(transactions)
├── has_many(tasks)
├── has_many(carousel_posts)
├── has_many(automations) [created_by]
└── has_many(notifications)

CAROUSEL_POSTS
├── belongs_to(carousel_templates)
├── has_many(carousel_slides)
├── has_many(carousel_versions)
├── has_many(carousel_publications)
└── belongs_to(users)

AUTOMATIONS
├── has_many(automation_steps)
├── has_many(automation_rules)
├── has_many(automation_enrollments)
└── belongs_to(users) [created_by]
```

### Foreign Key Summary

| From Table | To Table | Relationship | On Delete |
|---|---|---|---|
| operations.property_id | properties.id | Many-to-one | SET NULL |
| operations.client_id | clients.id | Many-to-one | SET NULL |
| operations.user_id | users.id | Many-to-one | CASCADE |
| clients.broker_id | brokers.id | Many-to-one | SET NULL |
| clients.user_id | users.id | Many-to-one | CASCADE |
| transactions.property_id | properties.id | Many-to-one | SET NULL |
| transactions.deal_id | deals.id | Many-to-one | SET NULL |
| carousel_posts.template_id | carousel_templates.id | Many-to-one | CASCADE |
| carousel_slides.carousel_post_id | carousel_posts.id | Many-to-one | CASCADE |
| automation_enrollments.automation_id | automations.id | Many-to-one | CASCADE |
| automation_enrollments.client_id | clients.id | Many-to-one | CASCADE |

---

## Column Type Distribution

| Type | Count | Examples |
|---|---|---|
| **integer** | ~250 | id, order, bedrooms, parking, scoring values |
| **varchar** | ~180 | name, email, phone, status, type, slug |
| **text** | ~80 | description, content, notes, JSON fields, body |
| **numeric** | ~60 | price, amount, percentage, area, temperature |
| **datetime** | ~120 | created_at, updated_at, sent_at, closed_at |
| **date** | ~30 | start_date, end_date, lease_start_date |
| **tinyint(1)** | ~40 | is_active, is_featured, can_read, boolean flags |
| **timestamp** | ~10 | last_activity, last_sync_at |
| **json** | ~20 | metadata, config, custom_data fields |

---

## Enum & Status Fields

### Operation Types
```
type: venta | renta | captacion
phase: prospecting | negotiation | closing | closed
stage: lead → viewing → offer → signed → funded → closed (17 stages)
status: active | completed | cancelled | on_hold
```

### Property Status
```
status: active | sold | rented | inactive
property_type: house | apartment | land | commercial | development
operation_type: venta | renta | venta_anticipada
```

### Client Status
```
lead_temperature: hot | warm | cold
priority: high | medium | low
interest_types: venta | renta | investment
```

### Automation Status
```
status: draft | active | paused | completed
trigger_type: lead | operation | date | event
```

---

## Indexing Strategy

**High-cardinality indexes (for filtering):**
- operations.user_id, operations.property_id, operations.client_id
- clients.email, clients.user_id
- properties.status, properties.operation_type
- transactions.user_id, transactions.date

**Unique constraints:**
- users.email (unique)
- clients.email (unique per broker)
- contract_templates.slug (unique)
- carousel_templates.slug (unique)
- posts.slug (unique)

**Composite indexes:**
- (market_colonia_id, snapshot_date) for market history queries
- (automation_id, client_id) for enrollment lookups
- (property_id, order) for photo ordering

---

## Scaling Considerations

### Current State
- SQLite (development) — 108 tables, ~1000 sample records
- Suitable for: local development, small data volumes
- Limitation: no concurrent writes, no replication

### Production Readiness
- **Database:** PostgreSQL 13+ recommended
- **Connection pooling:** PgBouncer (minimum 20 connections)
- **Partitioning candidates:**
  - transactions (by date range)
  - operation_comments (by operation_id)
  - carousel_slides (by carousel_post_id)
- **Archive candidates (>2 years old):**
  - completed operations
  - old carousel_posts
  - deprecated carousel_versions
- **Read replicas:** for reporting queries (valuations, market analysis)

### Query Optimization

**Common bottlenecks & solutions:**
1. **Operations with all relationships:** 
   - Add index on (user_id, status, created_at)
   - Cache frequently accessed operations

2. **Client search (by name/email/phone):**
   - Full-text search index on clients(name, email, phone)
   - Or: Elasticsearch/MeiliSearch for advanced search

3. **Carousel slide rendering:**
   - Cache rendered_image_path (avoid re-generation)
   - Queue system for batch rendering

4. **Lead scoring:**
   - Materialized view for lead_scores (refresh hourly)
   - Cache Redis for top 1000 leads

---

## Security Considerations

### PII & Sensitive Data
- **users.password** — Hashed (bcrypt), never exposed
- **clients.whatsapp, phone** — Access-controlled per role
- **custom_password_resets.token** — Cryptographically secure
- **google_signature_requests** — Contains email addresses of signers
- **mifiel_documentos** — May contain signed legal documents

### Audit & Compliance
- All critical tables have created_at/updated_at
- operation_stage_logs provides full transaction history
- legal_acceptances tracks signature dates and methods
- automation_step_logs enables audit trail for automations

### Access Control
- Role-based: users.role (supervisor/agent/admin)
- Capability-based: users.can_read/can_edit/can_delete
- Client assignment: clients.assigned_user_id restricts viewing

---

## Database Maintenance

### Regular Tasks
1. **Cleanup old records:** failed_jobs, sessions, cache (>30 days)
2. **Optimize statistics:** VACUUM, ANALYZE (PostgreSQL)
3. **Monitor table sizes:** especially carousel_slides, transactions
4. **Validate referential integrity:** check orphaned foreign keys
5. **Test backup/restore:** monthly recovery drills

### Monitoring Metrics
- Largest tables: operations, carousel_slides, transactions, clients
- Slowest queries: complex JOINs on carousel + properties
- Cache hit rate: measure for frequently queried tables
- Queue backlog: jobs, failed_jobs, automation_enrollments

---

## Implementation Notes

1. **Migrations are idempotent:** Each migration can run multiple times safely
2. **Seeders available:** See `database/seeders/` for test data
3. **Model relationships:** All defined in `app/Models/` (93 models)
4. **Soft deletes:** Not used; operations use status=cancelled instead
5. **Model caching:** Consider query caching for frequently accessed models (clients, properties)

