# IT105 Final Project - Inventory & Sales System

## Project Description
A database-driven inventory and sales management system for retail businesses. Tracks products, customers, sales transactions, and user roles (admin, cashier, inventory manager). Designed to handle 100,000+ rows and demonstrate ACID transactions, triggers, indexing, and role-based access.

## Group Members
- Marial Angel Zuniega - Project Manager
- Francheska Faith Batalla - Database Administrator
- Mary Franxine Nicol - SQL Developer
- Akisha Miel Reomalis - QA/Tester
- Rafael Baltasar - Documentation Lead
- Christofer Baldano - Security Officer

## Technology Stack
- Database: MySQL (XAMPP)
- Backend: PHP (planned)
- Frontend: HTML/CSS/JS (minimal)
- Version Control: GitHub

---

## Phase 1 – Planning and Design (Week 10)

### Deliverables
- ER Diagram: `/erd/ERD.png`
- Project Scope Document: `/documentation/Project_Scope.pdf`
- Initial Schema (normalized to 3NF): `/schema/schema.sql`
- Group Member Roles: `/documentation/Group_Member_Roles.pdf`

### Key Achievements
- Designed 5 normalized tables: `users`, `products`, `customers`, `sales`, `sale_items`
- Defined primary keys, foreign keys, and CHECK constraints
- Established many-to-many relationship via `sale_items` (junction table)

---

## Phase 2 – Schema Implementation & Data Population (Week 11)

### Deliverables
- Final Schema with all constraints: `/schema/schema.sql`
- Dataset (100,000+ rows): `/data/dataset.sql`
- Row count verification screenshot: `/documentation/row_counts.png`
- Performance baseline (without indexes): `/documentation/performance_baseline.txt`
- NoSQL Reflection Essay: `/documentation/nosql_reflection.pdf`

### Key Achievements
- Implemented physical schema in MySQL (XAMPP)
- Generated 100,000+ rows in `sale_items` using Python Faker script
- Verified referential integrity and row counts
- Baseline query execution time recorded for future optimization

---

## Phase 3 – BI Queries & Data Warehousing (Week 12)

### Deliverables
- BI Queries (5 advanced analytical queries): `/queries/bi_queries.sql`
- Star Schema SQL script: `/queries/star_schema.sql`
- Star Schema diagram: `/queries/star_schema_diagram.png`
- Query test results: `/documentation/query_test_results.txt`
- Insight Documentation Report: `/documentation/insight_report.pdf`

### Key Achievements
- Created 5 aggregation queries using `SUM`, `COUNT`, `AVG`, `GROUP BY`, `HAVING`, `JOIN`s, and date grouping (`MONTH`, `YEAR`)
- Designed a star schema with one fact table (`star_fact_sales`) and three dimensions (`dim_date`, `dim_customer`, `dim_product`)
- Documented business insights and dashboard recommendations

---

## How to Import the Database

1. Install XAMPP and start Apache & MySQL.
2. Open phpMyAdmin → create database `inventory_sales_db`.
3. Import `/schema/schema.sql` (tables and constraints).
4. Import `/data/dataset.sql` (100k+ rows).
5. Run `SELECT COUNT(*) FROM sale_items;` to verify ≥100,000 rows.

## How to Run BI Queries

1. Ensure the database is imported.
2. Open `/queries/bi_queries.sql` in phpMyAdmin SQL tab.
3. Execute each query to see analytical results.

## How to Create the Star Schema

1. Run `/queries/star_schema.sql` in the same database (tables will have `star_` prefix).
2. Populate fact and dimension tables using `INSERT INTO ... SELECT` from the normalized tables.

---
##  Phase 4: Cloud Deployment
**Status:** Complete

### Repository Organization
* **[/backup](./backup)**: Contains database snapshots for disaster recovery.
* **[/cloud](./cloud)**: Documentation and screenshots of the cloud migration.

### Deployment Summary
The database is now live on **Railway**. Connection was verified by querying the `order_items` table, confirming a total of **99,856** records.

**Documentation:** [View Cloud Deployment PDF](./cloud/cloud_deployment.pdf)

**Phase 5 – Database Optimization & Verification (Week 14)**
Deliverables
Optimized SQL script (with indexes): /queries/optimized_queries.sql

Performance comparison log: /documentation/optimized_performance.txt   

QA Verification report: /documentation/verification_phase5.txt   

Execution plan screenshots: /documentation/explain_plan_after.png

Key Achievements
Implemented a strategic indexing plan on orders(order_date), order_items(order_id), order_items(product_id), and orders(customer_id) to eliminate full table scans.  

Achieved a 42% performance gain on monthly revenue trends, reducing execution time from 0.7543s to 0.4331s.  

Optimized the top-selling products query, resulting in a 61% speed increase from 0.8434s to 0.3247s.  

Improved average order value calculations by 52%, bringing execution time down to 0.1215s.  

Conducted full QA verification to ensure all table joins and indexes are functional with zero SQL errors.  

Verified significant performance improvements in Year-over-Year trend analysis, reducing query time from 0.2875s to 0.1810s.


---

## Repository Structure
/
├── schema/ # Database schema (Phase 1 & 2)
│ └── schema.sql
├── data/ # Large dataset (Phase 2)
│ └── dataset.sql
├── erd/ # ER Diagram (Phase 1)
│ └── ERD.png
├── queries/ # BI queries & star schema (Phase 3)
│ ├── bi_queries.sql
│ ├── star_schema.sql
│ └── star_schema_diagram.png
├── documentation/ # Reports, screenshots, reflections
│ ├── Project_Scope.pdf
│ ├── Group_Member_Roles.pdf
│ ├── row_counts.png
│ ├── performance_baseline.txt
│ ├── nosql_reflection.pdf
│ ├── query_test_results.txt
│ └── insight_report.pdf
├── backup/ # For Phase 4
├── cloud/ # For Phase 4
└── README.md


---

## Upcoming Phases
- **Phase 4 (Week 13):** Cloud Backup & Emerging Technologies
- **Phase 5 (Week 14):** Optimization & Performance Tuning (indexing)
- **Phase 6 (Week 15):** Backup & QA Finalization
- **Phase 7 (Week 16):** Final Submission & Presentation

---

## Instructor
Sir Red (GitHub: [guired513](https://github.com/guired513))

## Repository Link
https://github.com/zuniegamarial/IT105-FinalGroupProject-Mary-and-her-little-lambs-2a
