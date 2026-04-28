# Home del Valle — Complete System Documentation Index

**Generated:** April 27, 2026  
**Scope:** Database architecture, 108 tables, 164 migrations, 93 models  
**Audience:** Senior engineers, architects, product leads

---

## 📚 Documentation Map

### 1. DATABASE_SCHEMA.md (Comprehensive Reference)
**What it covers:**
- All 108 tables organized into 18 module categories
- Column definitions with types and nullability
- Foreign key relationships and constraints
- Enum values and status fields
- Indexing strategy and scaling considerations
- Security, maintenance, and implementation notes

**When to use:**
- Implementing new features requiring database changes
- Understanding table relationships
- Adding constraints or indexes
- Compliance/security audits
- Scale planning for production

**Size:** ~400 lines | **Depth:** Comprehensive

---

### 2. SCHEMA_QUICK_REFERENCE.md (Developer Cheat Sheet)
**What it covers:**
- Five core tables (operations, properties, clients, users, carousel_posts)
- Operation pipeline stages (17 states)
- Common query patterns with code examples
- Enum values cheat sheet (quick lookup)
- Critical paths through data
- Large tables to watch (performance concerns)
- Data integrity rules
- Migration strategy
- Entity relationship diagram (compact)

**When to use:**
- Daily development work
- Quick enum value lookup
- Writing queries
- Performance debugging
- Understanding data flow

**Size:** ~300 lines | **Depth:** Practical

---

### 3. ARCHITECTURE_ANALYSIS.md (Strategic Assessment)
**What it covers:**
- System design patterns (operation-centric pipeline)
- Module-by-module assessment (🟢🟡🔴 ratings)
- Data quality & integrity analysis
- Query hotspots and performance optimization
- Database size projections (5-year growth)
- Security analysis and PII locations
- Feature roadmap (phases 1-4)
- Migration strategy for next major version
- Recommendations (immediate, short-term, medium-term, long-term)

**When to use:**
- Architecture decisions
- Performance optimization planning
- Security audits
- Budget/resource planning
- Product roadmap alignment
- Pre-scaling assessments

**Size:** ~600 lines | **Depth:** Strategic

---

## 🗂️ Module Breakdown (18 Categories)

| Module | Tables | Status | Key Entity | Assessment |
|--------|--------|--------|-----------|-----------|
| **Core Foundation** | 3 | ✅ Complete | users, sessions, password_reset_tokens | 🟢 Solid |
| **Property Management** | 10 | ✅ Complete | properties, property_photos, property_qr_codes | 🟡 Good |
| **Client & Lead Mgmt** | 9 | ✅ Complete | clients, client_emails, lead_scores | 🟡 Good |
| **Operation Pipeline** | 8 | ✅ Complete | operations (core), operation_stage_logs | 🟡 Needs Refactor |
| **Deals & Transactions** | 4 | ✅ Complete | transactions, deals, commissions | 🟢 Good |
| **Rental Management** | 2 | ✅ Complete | rental_processes, rental_stage_logs | 🟢 Good |
| **Marketing & Campaigns** | 6 | ✅ Complete | marketing_campaigns, marketing_channels | 🟡 Incomplete Analytics |
| **Carousel & Social** | 11 | ✅ Complete | carousel_posts, carousel_slides, carousel_templates | 🟢 Excellent |
| **Blog & Content** | 6 | ✅ Complete | posts, help_articles, tags | 🟡 Unused |
| **Help & Onboarding** | 4 | ⚠️ Skeleton | help_tips, help_onboarding_progress | 🔴 No Content |
| **Legal & Contracts** | 6 | ✅ Complete | contracts, legal_documents, mifiel_documentos | 🟢 Good |
| **E-Signature** | 2 | ✅ Complete | google_signature_requests, poliza_juridicas | 🟢 Good |
| **Valuations & Market** | 7 | 🟡 Partial | property_valuations, market_zones | 🟡 Incomplete |
| **Referrals & Partners** | 2 | ⚠️ Skeleton | referrers, referrals | 🔴 Skeleton |
| **Automation & Workflows** | 8 | ✅ Complete | automations, automation_steps, automation_enrollments | 🟡 Growing |
| **AI & Integrations** | 4 | 🟡 Partial | ai_agent_configs, carousel_image_prompts | 🟡 Partial |
| **Notifications** | 5 | ✅ Complete | notifications, user_mail_settings | 🟢 Good |
| **Site Config & Media** | 7 | ✅ Complete | site_settings, menus, pages, media | 🟢 Good |

---

## 🎯 Quick Lookup by Use Case

### "I need to understand the sales pipeline"
1. Start: DATABASE_SCHEMA.md → **Module 4: Operation Pipeline**
2. Reference: SCHEMA_QUICK_REFERENCE.md → **Operation Pipeline Stages**
3. Dive deeper: DATABASE_SCHEMA.md → **operation_stage_logs, operation_comments, operation_checklist_items**

### "I'm building a new feature; what tables do I need?"
1. SCHEMA_QUICK_REFERENCE.md → **The Five Core Tables**
2. SCHEMA_QUICK_REFERENCE.md → **Critical Paths Through Data**
3. DATABASE_SCHEMA.md → Find relevant module

### "Why is my query slow?"
1. ARCHITECTURE_ANALYSIS.md → **Query Hotspots**
2. SCHEMA_QUICK_REFERENCE.md → **Large Tables to Watch**
3. DATABASE_SCHEMA.md → **Indexing Strategy**

### "Can we add 10,000 more operations/month?"
1. ARCHITECTURE_ANALYSIS.md → **Database Size Projections**
2. ARCHITECTURE_ANALYSIS.md → **Performance Analysis**
3. ARCHITECTURE_ANALYSIS.md → **Recommendations: Immediate Actions**

### "Is this secure? What's the PII risk?"
1. ARCHITECTURE_ANALYSIS.md → **Security Analysis**
2. DATABASE_SCHEMA.md → **Security Considerations**
3. Review: google_signature_requests, mifiel_documentos, users.password

### "Which tables have problems?"
1. ARCHITECTURE_ANALYSIS.md → **Module Analysis** (sort by 🔴 and 🟡)
2. Review: Referrals, Help, Valuation (gaps identified)
3. ARCHITECTURE_ANALYSIS.md → **Critical Gaps**

### "What's the next big project?"
1. ARCHITECTURE_ANALYSIS.md → **Feature Roadmap** (Phase 1-4)
2. ARCHITECTURE_ANALYSIS.md → **Recommendations Summary**
3. DATABASE_SCHEMA.md → Review proposed modules

---

## 🔍 Key Tables at a Glance

### The Five Core Tables (80% of logic)

**1. operations** (29 columns)
- **Purpose:** Central hub; every transaction flows here
- **Key fields:** type (venta/renta/captacion), stage, status, amount, commission_percentage
- **Links to:** properties, clients, users, brokers
- **Caution:** 29 columns with many conditionals; consider refactoring

**2. properties** (38 columns)
- **Purpose:** Real estate inventory
- **Key fields:** price, bedrooms, bathrooms, area, status, operation_type
- **Links to:** operations, clients, brokers, market_colonias
- **Caution:** Duplicated photo storage (property_photos vs property_images)

**3. clients** (27 columns)
- **Purpose:** Leads, buyers, renters, owners (all in one table)
- **Key fields:** email, budget_min/max, property_type, lead_temperature, broker_id, assigned_user_id
- **Links to:** operations, marketing_channels, users, brokers
- **Caution:** Ambiguous "client type"; both buyer and seller clients mixed

**4. users** (33 columns)
- **Purpose:** Staff, agents, admins
- **Key fields:** email, role (admin/supervisor/agent/client), permissions (can_read/edit/delete)
- **Links to:** operations, clients, tasks, carousel_posts
- **Caution:** Cascading delete removes all user's operations if deleted

**5. carousel_posts** (19 columns)
- **Purpose:** Instagram carousel content (AI-generated or manual)
- **Key fields:** type (property/market/testimonial), status (draft/pending/approved/published), template_id
- **Links to:** carousel_slides, carousel_versions, carousel_publications
- **Strength:** Well-structured with versions and multi-channel support

---

## 📊 System Statistics

| Metric | Value |
|--------|-------|
| **Total Tables** | 108 |
| **Total Migrations** | 164 |
| **Total Models** | 93 |
| **Total Routes** | 357 |
| **Total Controllers** | 98 |
| **Module Categories** | 18 |
| **Foreign Key Relationships** | ~60 |
| **Unique Constraints** | ~20 |
| **Check Constraints** | ~5 |
| **Database Size (est.)** | 50-100 MB (dev) |

---

## ✅ Quality Assessment

### By Module
- **🟢 Excellent (4):** Core Foundation, Deals, Rental, Carousel
- **🟡 Good with Gaps (9):** Properties, Clients, Operations, Marketing, Blog, Legal, Notifications, Site Config, Automation
- **🟠 Incomplete (3):** Valuations, Leads/Scoring, AI/Integrations
- **🔴 At-Risk (2):** Help/Onboarding, Referrals

### By Criterion
- **Data Integrity:** 8/10 (needs more constraints)
- **Normalization:** 7/10 (operations table bloated)
- **Documentation:** 8/10 (this analysis)
- **Performance:** 6/10 (missing indexes, slow carousel queries)
- **Security:** 7/10 (PII not encrypted at rest)
- **Scalability:** 7/10 (archival needed at >500MB)

**Overall Assessment:** 7.3/10  
**With recommendations:** 8.8/10

---

## 🚀 Implementation Priorities

### Sprint 1: Foundation (Immediate)
1. Add missing indexes (operations user_id, property_id, client_id)
2. Audit referential integrity (run orphan detection)
3. Document enum values (create seed data)

### Sprint 2: Stability (1-2 Weeks)
4. Consolidate photo management (property_photos vs property_images)
5. Complete lead scoring (implement scoring engine)
6. Finish campaign attribution (clients → campaigns)

### Sprint 3: Performance (1 Month)
7. Archive old carousel versions (>6 months)
8. Optimize carousel rendering (batch jobs)
9. Add geospatial search (PostGIS)

### Future: Refactoring (Next Major Version)
10. Refactor operations table (split by type if growth continues)
11. Enhance valuation system (market_colonia_metrics)
12. Build analytics layer (materialized views)

---

## 📖 How to Use These Docs

### For Different Roles

**Product Manager:**
1. Read: ARCHITECTURE_ANALYSIS.md → Feature Roadmap
2. Reference: DATABASE_SCHEMA.md → Module descriptions
3. Plan: Use module status (🟢🟡🔴) to estimate effort

**Backend Engineer:**
1. Read: SCHEMA_QUICK_REFERENCE.md → Your turn, bookmark it
2. Reference: DATABASE_SCHEMA.md → When adding tables
3. Debug: SCHEMA_QUICK_REFERENCE.md → Common query patterns

**DevOps/DBA:**
1. Read: ARCHITECTURE_ANALYSIS.md → Performance & Scaling
2. Monitor: SCHEMA_QUICK_REFERENCE.md → Monitoring queries
3. Plan: ARCHITECTURE_ANALYSIS.md → Database size projections

**Security Engineer:**
1. Read: ARCHITECTURE_ANALYSIS.md → Security Analysis
2. Audit: DATABASE_SCHEMA.md → Security Considerations
3. Review: Tables with PII (google_signature_requests, mifiel_documentos)

**New Team Member:**
1. Start: SCHEMA_QUICK_REFERENCE.md → Overview
2. Deep dive: DATABASE_SCHEMA.md → Your module
3. Practice: SCHEMA_QUICK_REFERENCE.md → Common query patterns

---

## 🔗 Quick Links

| Document | Size | Focus | Link |
|----------|------|-------|------|
| DATABASE_SCHEMA.md | 400 lines | Complete reference | [Link](.claude/DATABASE_SCHEMA.md) |
| SCHEMA_QUICK_REFERENCE.md | 300 lines | Developer cheat sheet | [Link](.claude/SCHEMA_QUICK_REFERENCE.md) |
| ARCHITECTURE_ANALYSIS.md | 600 lines | Strategic assessment | [Link](.claude/ARCHITECTURE_ANALYSIS.md) |
| SYSTEM_DOCUMENTATION_INDEX.md | This file | Navigation & overview | [Link](.claude/SYSTEM_DOCUMENTATION_INDEX.md) |

---

## 📝 Maintenance & Updates

**Last Updated:** April 27, 2026  
**Next Review:** October 2026 (6 months)  
**Trigger Update When:**
- New migration added (every 2-3 days)
- Schema refactoring (major version bump)
- New module created (quarterly)
- Performance issues discovered (as-needed)

---

## 🎓 Learning Path

**Beginner (First Week)**
1. SCHEMA_QUICK_REFERENCE.md → The Five Core Tables
2. SCHEMA_QUICK_REFERENCE.md → Operation Pipeline Stages
3. DATABASE_SCHEMA.md → Module 1 (Core Foundation)

**Intermediate (Second Week)**
4. SCHEMA_QUICK_REFERENCE.md → Critical Paths Through Data
5. DATABASE_SCHEMA.md → Modules 2-4 (Property, Client, Operations)
6. SCHEMA_QUICK_REFERENCE.md → Common query patterns

**Advanced (Third Week)**
7. ARCHITECTURE_ANALYSIS.md → Design Patterns & Module Analysis
8. ARCHITECTURE_ANALYSIS.md → Query Hotspots
9. DATABASE_SCHEMA.md → Remaining modules + relationships

**Expert (Ongoing)**
10. ARCHITECTURE_ANALYSIS.md → Security, Performance, Recommendations
11. SCHEMA_QUICK_REFERENCE.md → Monitoring queries (proactive)
12. Contribute to architecture evolution

---

## Questions?

If you can't find what you're looking for:

1. **Search by table name** in DATABASE_SCHEMA.md (Ctrl+F)
2. **Search by module** in ARCHITECTURE_ANALYSIS.md
3. **Search by query pattern** in SCHEMA_QUICK_REFERENCE.md
4. **Search by role** in this index (How to Use These Docs)

---

## 📞 Document Contributors

- **Analysis & Documentation:** Claude (AI Assistant) — April 2026
- **System Architect:** [Alejandro Lechuga] — Ongoing
- **Database Design:** Home del Valle Team — Since 2025

---

**Total Documentation Size:** ~1,300 lines | **Estimated Read Time:** 30-60 minutes (selective) | **Total Information Density:** High

