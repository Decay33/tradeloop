# TradeLoop v1.1 Pro Polish Plan

This is an addendum to TRADELOOP_PROJECT_PLAN.md.

The goal is to improve the existing TradeLoop demo without turning it into a complex CRM.

Do not rebuild the application.
Do not replace the existing architecture.
Do not add real SMS, real email, Stripe, QuickBooks, Zapier, AI, or a customer portal.

Keep the app simple.

## Core Flow

The primary flow is:

Customer → Estimate → Job → Invoice/Payment → Complete Job → Follow-Up

The app must also support:

Customer → Job
Customer → Invoice
Customer → Follow-Up
Customer → Estimate

Estimates are optional. A job should not require an estimate.

## Estimate to Job/Invoice Flow

Improve the existing Create Job + Invoice action.

When a user clicks Create Job + Invoice from an accepted estimate, show a modal before creating records.

The modal must allow review/edit of:

- job title
- scheduled date
- assigned team member
- job address
- job notes
- create invoice now checkbox
- invoice due date
- invoice line items
- invoice discount
- invoice tax
- invoice total

Buttons:

- Create Job + Invoice
- Create Job Only
- Cancel

After creation:

- Estimate detail must show linked job and linked invoice cards.
- Job detail must show linked estimate and linked invoice cards.
- Invoice detail must show linked estimate and linked job cards.
- Prevent duplicate job/invoice creation from the same estimate.

## Direct Job Creation

Jobs can be created directly from:

- Jobs page
- Customer detail page

Direct job form fields:

- customer
- service type
- job title
- scheduled date
- assigned team member
- job address
- notes
- quoted price

Add optional jobs.quoted_total_cents.

If job is created from estimate, quoted_total_cents defaults to estimate.total_cents.

The official customer balance comes from the invoice, not the quoted job price.

## Job Detail Invoice Improvements

Job detail must show a clear invoice summary card.

If invoice exists, show:

- invoice number
- invoice status
- invoice total
- amount paid
- balance due
- due date
- record payment button
- send invoice email button
- view invoice button

If no invoice exists, show:

- No invoice linked
- Create Invoice From Job button

## Invoice Improvements

Invoices can be linked to jobs, but invoices can also exist without jobs.

Keep one primary invoice per job for now.

Support multiple payments per invoice.

Invoice detail must show payments clearly:

- date
- amount
- method
- notes
- recorded by

Add button:

- Record Another Payment

Payment methods:

- cash
- check
- credit_card
- bank_transfer
- other

## Invoice PDF and Simulated Email

Add buttons on invoice detail:

- Download PDF
- Send Invoice Email

In demo mode:

- no real email is sent
- clicking Send Invoice Email creates an invoice_send_events record
- status is simulated_sent
- invoice.sent_at is set
- audit log entry is created
- user sees confirmation that the email was simulated

Create invoice_send_events table:

- id
- business_id
- invoice_id
- user_id nullable
- recipient
- subject
- body
- status
- attachment_path nullable
- sent_at nullable
- created_at
- updated_at

For PDF:

Preferred: generate a simple PDF from invoice print view.
Fallback: use print-friendly invoice view and allow browser Save as PDF.

Do not spend excessive time on PDF styling.

## Customer Detail Quick Actions

Customer detail should have quick actions:

- Create Estimate
- Create Job
- Create Invoice
- Create Follow-Up

Each action should prefill the selected customer.

## Manual Follow-Ups

Follow-ups can be created manually, not only from completed jobs.

Manual follow-ups can be created from:

- customer detail
- estimate detail
- job detail
- follow-ups page

Manual follow-up fields:

- customer
- optional estimate
- optional job
- channel
- purpose
- scheduled date
- message body

Add purpose:

- sales_follow_up

Update followup_messages:

- job_id should be nullable
- add estimate_id nullable
- add created_by_user_id nullable
- add is_manual boolean default false

Customer_id remains required.

Manual follow-ups must respect SMS/email consent.

If consent is missing, save the follow-up as skipped with a skip reason.

## Job Completion Follow-Up Review

When user clicks Complete Job, show a modal before saving.

The modal should include:

- completed date
- generated follow-up schedule
- editable scheduled dates
- channel
- purpose
- recipient
- message preview
- remove row action

Buttons:

- Complete Job + Schedule Follow-Ups
- Complete Job Only
- Cancel

Default follow-ups should be pre-populated from existing service-type rules.

User can adjust follow-up dates before saving.

User can remove generated follow-ups.

User can complete the job without follow-ups.

Do not duplicate follow-ups if the job was already completed and followups_scheduled_at is set.

## Smart Filters

Add simple preset filters.

Estimate filters:

- All
- Draft
- Sent
- Accepted
- Declined
- Expired
- Needs Follow-Up
- Accepted - No Job
- Accepted - Has Job
- Sent 30+ Days Old

Definitions:

Needs Follow-Up = sent/open estimate older than 7 days with no linked job.
Sent 30+ Days Old = sent/open estimate older than 30 days.

Invoice filters:

- All
- Draft
- Sent
- Partially Paid
- Paid
- Unpaid
- Due Soon
- Past Due
- 30+ Days Overdue
- 60+ Days Overdue

Urgency labels:

- Due Soon
- Past Due
- Very Overdue
- Critical

Job filters:

- All
- Scheduled
- In Progress
- Completed
- Canceled
- No Invoice
- Unassigned
- Assigned To Me

Follow-up filters:

- Due Today
- Upcoming
- Sales Follow-Ups
- Job Follow-Ups
- Sent
- Skipped
- Canceled

## Reports Improvements

Improve reports without adding complexity.

Reports must support date ranges:

- Today
- Yesterday
- This Week
- Last Week
- This Month
- Last Month
- This Year
- Custom

Report sections:

- Daily Snapshot
- Sales Pipeline
- Job Activity
- Collections
- Follow-Up Activity
- Service Breakdown

Daily Snapshot table:

- date
- estimates created
- estimate value
- jobs created
- jobs completed
- invoices created
- payments collected
- follow-ups sent

Numbers should link to filtered records when practical.

Sales Pipeline:

- open estimate value
- accepted estimate value
- estimates needing follow-up
- accepted estimates without jobs
- estimate win rate

Job Activity:

- jobs created
- jobs scheduled
- jobs started
- jobs completed
- jobs canceled
- jobs with no invoice
- jobs by assigned team member

Collections:

- payments collected
- unpaid invoices
- past due invoices
- 30+ days overdue
- 60+ days overdue
- invoice aging table

Follow-Up Activity:

- follow-ups scheduled
- follow-ups sent/simulated
- follow-ups skipped
- review requests sent
- sales follow-ups due
- repeat-service follow-ups due

Service Breakdown:

- revenue by service type
- jobs by service type
- average invoice by service type
- repeat revenue opportunity by service type

## Team Members

Team members are optional.

Do not force team setup during onboarding.

Add team management under:

Settings → Team

Owner can add/edit/deactivate team members.

Team member fields:

- name
- email
- cell phone
- role
- temporary password
- active/inactive
- permission checkboxes

Roles:

- owner
- manager
- field_staff
- custom

Add users.phone nullable.

Add business_user.permissions json nullable.
Add business_user.is_active boolean default true.

Permission checkboxes:

- view_dashboard
- manage_customers
- create_estimates
- manage_estimates
- create_jobs
- start_jobs
- complete_jobs
- manage_invoices
- record_payments
- manage_followups
- view_reports
- manage_settings
- manage_team

Owner always has all permissions.

Field Staff default permissions:

- view_dashboard
- manage_customers
- create_jobs
- start_jobs
- complete_jobs
- manage_followups

Manager default permissions:

- everything except manage_team

Custom permissions are selected manually.

## Job Assignment

Add optional job assignment.

Jobs table additions:

- assigned_user_id nullable
- started_by_user_id nullable
- completed_by_user_id nullable

Job forms should allow assigning a team member.

Job detail should show:

- assigned to
- started by
- completed by

Job list should support:

- assigned to me
- unassigned
- assigned team member filter

## Security

All new features must respect business_id isolation.

Team members can only access their own business.

Permissions must be checked server-side using policies or middleware.

Do not rely on hiding buttons only.

Every new query must be scoped to current business.

## Demo Mode

Demo mode remains enabled.

No real email is sent.

Invoice email sending is simulated.

Follow-up sending is simulated.

Demo data should include:

- at least one assigned job
- at least one unassigned job
- at least one direct job without estimate
- at least one estimate with no job
- at least one old sent estimate needing follow-up
- at least one invoice with split payments
- at least one manual sales follow-up
- at least one simulated invoice email
- at least one field staff user

## Tests Required

Add or update tests for:

- direct job creation without estimate
- accepted estimate opens job/invoice creation flow
- duplicate job/invoice creation is prevented
- job detail shows linked invoice data
- estimate detail shows linked job data
- invoice supports multiple payments
- payment split updates balance correctly
- invoice email send is simulated in demo mode
- manual customer follow-up creation
- estimate-linked manual follow-up
- completing job with edited follow-up dates
- completing job without follow-ups
- smart estimate filters
- smart invoice filters
- report date ranges
- daily report counts
- team member creation
- team permissions
- field staff can complete jobs
- field staff cannot access reports/settings
- all new records are business-scoped

## Build Order

1. Add migrations.
2. Update models and relationships.
3. Update policies/permissions.
4. Improve estimate detail and create job/invoice modal.
5. Add direct job creation and customer quick actions.
6. Improve job detail invoice card.
7. Improve invoice payments UI.
8. Add invoice PDF/download and simulated email send.
9. Add manual follow-ups.
10. Add job completion follow-up review modal.
11. Add smart filters.
12. Improve reports.
13. Add optional team management.
14. Update demo seed data.
15. Add tests.
16. Run npm build and php artisan test.
17. Update README.

## Acceptance Criteria

TradeLoop v1.1 is complete when:

- jobs can be created without estimates
- estimates can still create jobs/invoices
- estimate-to-job/invoice creation uses a review modal
- estimate detail shows linked job/invoice
- job detail shows invoice/payment information clearly
- direct jobs can have quoted price
- invoices can be standalone or job-linked
- invoices support multiple payments
- invoice email send is simulated in demo mode
- invoice PDF/download works or print/save PDF fallback exists
- customer detail has create estimate/job/invoice/follow-up actions
- manual sales follow-ups work
- job completion previews follow-ups before saving
- follow-up dates can be edited before saving
- user can complete job without follow-ups
- estimate/invoice/job/follow-up smart filters work
- reports have useful date ranges and drill-down tables
- owner can add optional team members
- team permissions work
- field staff can start/complete jobs but cannot access restricted areas
- demo data includes the new flows
- all tests pass