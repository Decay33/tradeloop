\# TradeLoop Project Plan and Design Document



Version: 1.0  

Project Name: TradeLoop  

Project Root: `C:\\tradeloop`  

Primary Document Filename: `TRADELOOP\_PROJECT\_PLAN.md`  

Primary Goal: Full working demo MVP  

Hosting Target: Cloudways  

Application Type: Web-based SaaS  



This document is the source of truth for the TradeLoop demo MVP. Codex should read this file before making code changes. If any previous chat instructions conflict with this document, this document wins.



\---



\# 1. Product Summary



TradeLoop is a simple web-based SaaS app for small contractors and home improvement providers.



The product helps contractors:



\- track customers

\- create estimates

\- convert accepted estimates into jobs and invoices

\- record payments

\- mark jobs complete

\- automatically schedule thank-you messages, review requests, and repeat-service reminders

\- see sales, revenue, unpaid invoices, overdue invoices, and repeat-revenue opportunities



The core promise is:



> Finish the job. TradeLoop handles the follow-up.



The value proposition is:



> TradeLoop helps contractors turn completed jobs into reviews, repeat work, and better sales tracking automatically.



TradeLoop should not feel like a complicated CRM. It should feel like a simple contractor tool that helps the business owner make more money from customers they already served.



\---



\# 2. Target Users



TradeLoop is built for small home service businesses with roughly 1 to 10 employees.



Primary trade categories:



\- General handyman

\- Pavers

\- Asphalt and sealcoating

\- Painters

\- Roofers

\- Landscapers

\- Pressure washers

\- Flooring installers

\- Gutter companies

\- Fence builders

\- HVAC contractors

\- Deck builders and deck repair contractors

\- Remodelers

\- Garage door installers

\- Window and door installers

\- Similar home improvement providers



The product must not be limited to pavers. It must work broadly for home service businesses.



\---



\# 3. Product Principles



TradeLoop must follow these principles:



1\. Simplicity first.

2\. Mobile-friendly web app first.

3\. Fast login and low-friction use.

4\. Contractors should understand the dashboard within 30 seconds.

5\. Every completed job should be capable of creating future follow-up value.

6\. Sales and revenue numbers should be visible without accounting complexity.

7\. No real SMS, real email, Stripe, QuickBooks, or advanced integrations in the first demo.

8\. Security and business data isolation must be implemented from the beginning.

9\. Avoid CRM jargon.

10\. Do not overbuild.



Plain-language examples:



\- Use “Customers,” not “Contacts.”

\- Use “Follow-Ups,” not “Automations.”

\- Use “Jobs,” not “Opportunities.”

\- Use “Repeat Revenue,” not “Lifecycle Value.”



\---



\# 4. MVP Objective



Build a full working demo MVP, not a static prototype.



The demo must include:



\- secure contractor login

\- demo login button

\- real database records

\- business profile

\- customers

\- service types

\- estimates

\- estimate line items

\- estimate status flow

\- accepted estimate to job and invoice conversion

\- invoices

\- invoice line items

\- manual payment recording

\- job status flow

\- job completion automation

\- follow-up templates

\- follow-up rules

\- follow-up messages

\- simulated SMS/email outbox

\- dashboard metrics

\- sales reports

\- repeat revenue opportunity reporting

\- demo data seeding

\- demo data reset command

\- tests



The demo must not send real SMS or real email. It must simulate these actions inside the app.



\---



\# 5. In-Scope Features for Demo MVP



\## 5.1 Authentication



The app must support:



\- email/password login

\- logout

\- password hashing through Laravel

\- session-based authentication

\- login rate limiting

\- CSRF protection

\- demo login button when `DEMO\_MODE=true`

\- authenticated app layout

\- role-aware navigation



Password reset and email verification can exist through Laravel defaults, but in demo mode emails should use the log mailer or be non-sending.



\## 5.2 Business Profile



Each user belongs to a business.



Business profile fields:



\- business name

\- trade type

\- phone

\- email

\- website

\- address

\- city

\- state

\- zip

\- timezone

\- Google review URL

\- Facebook review URL

\- default tax rate

\- default invoice terms



\## 5.3 Customers



Contractors can:



\- create customers

\- edit customers

\- soft-delete customers

\- search customers

\- view customer details

\- view customer history

\- save customer notes

\- store SMS consent

\- store email consent

\- track SMS/email opt-out timestamps



\## 5.4 Service Types



Contractors can manage service types.



Service types drive:



\- estimate categorization

\- job categorization

\- follow-up rules

\- reporting by service

\- repeat revenue estimates



\## 5.5 Estimates



Contractors can:



\- create estimates

\- add line items

\- edit line items

\- save drafts

\- mark estimates sent

\- mark estimates accepted

\- mark estimates declined

\- print estimates

\- convert accepted estimates into jobs and invoices



Estimate statuses:



```text

draft

sent

accepted

declined

expired

```



\## 5.6 Invoices



Contractors can:



\- create invoices manually

\- create invoices from accepted estimates

\- copy estimate line items to invoices

\- mark invoices sent

\- record partial payments

\- record full payments

\- void invoices

\- print invoices

\- view unpaid invoices

\- view overdue invoices



Invoice statuses:



```text

draft

sent

partially\_paid

paid

overdue

void

```



Reports should determine overdue invoices based on due date and balance, even if stored status has not been updated.



\## 5.7 Payments



Payments are manually recorded in the demo.



Payment methods:



```text

cash

check

credit\_card

bank\_transfer

other

```



Payment amount must not exceed the invoice balance due.



\## 5.8 Jobs



Contractors can:



\- create jobs from accepted estimates

\- view jobs

\- schedule jobs

\- start jobs

\- complete jobs

\- cancel jobs



Job statuses:



```text

scheduled

in\_progress

completed

canceled

```



Completing a job triggers follow-up automation.



\## 5.9 Follow-Up Automation



When a job is marked completed, TradeLoop must automatically schedule follow-up messages based on the job’s service type.



Example for driveway sealcoating:



```text

Day 1: thank-you

Day 3: review request

Month 11: reseal reminder

Month 24: repeat-service reminder

```



Example for general handyman:



```text

Day 1: thank-you

Day 3: review request

Month 6: check-in

Month 12: repeat-service reminder

```



\## 5.10 Simulated Message Outbox



The demo must show scheduled and simulated sent messages.



Channels:



```text

sms

email

```



In demo mode:



\- no SMS leaves the system

\- no email leaves the system

\- sending creates or updates database records only

\- status becomes `simulated\_sent`

\- message events are logged



\## 5.11 Dashboard and Reports



Contractors must be able to see:



\- revenue this month

\- open estimate value

\- accepted estimate value

\- unpaid invoices

\- overdue invoices

\- jobs completed

\- average job value

\- review requests sent

\- follow-ups due today

\- repeat revenue opportunity

\- revenue by service type

\- invoice aging

\- estimate win rate

\- repeat revenue pipeline



\---



\# 6. Out-of-Scope for Demo MVP



Do not build these in the first version:



\- real Twilio SMS

\- real email delivery through Resend, Postmark, Mailgun, SMTP, or similar

\- Stripe subscriptions

\- customer payment portal

\- customer login portal

\- QuickBooks integration

\- Zapier integration

\- AI message writing

\- crew time tracking

\- inventory

\- maps or GPS

\- native iOS app

\- native Android app

\- complex accounting

\- complex multi-location enterprise features

\- proposal e-signatures

\- document upload management

\- two-way texting

\- inbound SMS opt-out processing



These can be future features after the demo is validated.



\---



\# 7. Technical Stack



Use a Cloudways-friendly Laravel stack.



Recommended stack:



```text

Backend: Laravel

Frontend: React with Inertia.js

Styling: Tailwind CSS

Database: MySQL

Authentication: Laravel session-based auth

Queue: Laravel database queue

Scheduler: Laravel Scheduler

Email in demo: Laravel log mailer or no-op simulated sender

SMS in demo: simulated sender only

PDF in demo: print-friendly HTML views

Production hosting target: Cloudways

```



Do not use:



```text

Supabase

Firebase

Next.js as the main app

MongoDB

a separate persistent Node backend

API-token-only auth for the browser app

```



The app may use Node tooling only to compile frontend assets.



The app must not require a persistent Node server in production.



\---



\# 8. Recommended Laravel Structure



Use Laravel conventions wherever possible.



Recommended directories:



```text

app/

&#x20; Actions/

&#x20; Console/Commands/

&#x20; Http/Controllers/

&#x20; Http/Middleware/

&#x20; Http/Requests/

&#x20; Models/

&#x20; Policies/

&#x20; Services/

&#x20; Support/

database/

&#x20; factories/

&#x20; migrations/

&#x20; seeders/

resources/

&#x20; js/

&#x20;   Components/

&#x20;   Layouts/

&#x20;   Pages/

&#x20; views/

routes/

&#x20; web.php

tests/

&#x20; Feature/

&#x20; Unit/

```



Recommended service classes:



```text

App\\Services\\CurrentBusinessResolver

App\\Services\\EstimateCalculator

App\\Services\\InvoiceCalculator

App\\Services\\InvoicePaymentService

App\\Services\\EstimateConversionService

App\\Services\\FollowupScheduler

App\\Services\\TemplateRenderer

App\\Services\\DemoMessageSender

App\\Services\\ReportService

App\\Services\\AuditLogger

```



Recommended actions:



```text

App\\Actions\\AcceptEstimate

App\\Actions\\CreateJobAndInvoiceFromEstimate

App\\Actions\\CompleteJob

App\\Actions\\RecordInvoicePayment

App\\Actions\\ScheduleJobFollowups

App\\Actions\\ProcessDueFollowups

```



Use Form Request classes for validation.



Use Policies for authorization.



Use model factories for tests and seeders.



\---



\# 9. Environment Configuration



Create and maintain `.env.example`.



Required variables:



```env

APP\_NAME=TradeLoop

APP\_ENV=local

APP\_KEY=

APP\_DEBUG=true

APP\_URL=http://localhost



DB\_CONNECTION=mysql

DB\_HOST=127.0.0.1

DB\_PORT=3306

DB\_DATABASE=tradeloop

DB\_USERNAME=root

DB\_PASSWORD=



QUEUE\_CONNECTION=database



DEMO\_MODE=true

MAIL\_MAILER=log

SMS\_DRIVER=log



SESSION\_DRIVER=file

CACHE\_STORE=file

```



Production-style demo values:



```env

APP\_ENV=production

APP\_DEBUG=false

DEMO\_MODE=true

MAIL\_MAILER=log

SMS\_DRIVER=log

QUEUE\_CONNECTION=database

```



Do not commit a real `.env` file.



Do not hard-code secrets.



\---



\# 10. Cloudways Deployment Requirements



The app must be deployable as a normal Laravel app on Cloudways.



Deployment expectations:



\- Composer dependencies install with `composer install`.

\- Frontend dependencies install with `npm install`.

\- Frontend assets build with `npm run build`.

\- Laravel migrations run with `php artisan migrate --seed`.

\- Queue tables are created through migrations.

\- Scheduled commands run through Cloudways cron.

\- No persistent Node server is required after assets are built.

\- The app works behind HTTPS.

\- Storage link can be created with `php artisan storage:link` if file uploads/logos are implemented.



Cron example:



```bash

\* \* \* \* \* cd /path/to/tradeloop \&\& php artisan schedule:run >> /dev/null 2>\&1

```



Cloudways path will vary. Do not hard-code the production path.



\---



\# 11. Security Requirements



Security must be implemented from day one.



\## 11.1 Authentication



Use Laravel session-based authentication.



Requirements:



\- passwords stored only through Laravel hashing

\- no plain-text passwords

\- login rate limiting

\- CSRF protection

\- secure cookies in production

\- no auth tokens in localStorage

\- logout invalidates session

\- unauthenticated users cannot access app pages



\## 11.2 Authorization



Use roles:



```text

owner

manager

staff

```



Role permissions:



| Feature | Owner | Manager | Staff |

|---|---:|---:|---:|

| Dashboard | Yes | Yes | Limited |

| Customers | Full | Full | Create/Edit/View |

| Estimates | Full | Full | View only or no access |

| Invoices | Full | Full | No access |

| Payments | Full | Full | No access |

| Jobs | Full | Full | Create/Edit/View |

| Follow-Ups | Full | Full | View only |

| Reports | Full | Full | No access |

| Settings | Full | Limited | No access |

| Team Users | Full | No access | No access |



For the demo, seed at least one owner. Staff user is optional but recommended for role tests.



\## 11.3 Business Data Isolation



This is critical.



Every business-owned top-level table must include `business\_id`.



Child item tables should also include `business\_id` for defense in depth, even when they are linked through a parent.



Every query must be scoped to the logged-in user’s current business.



Do not rely on frontend filtering for security.



Route model binding must not allow cross-business access.



Controllers must either:



1\. query models through the current business scope,

2\. call policies that verify the model belongs to the current business,

3\. or do both.



Business A must never be able to view, edit, delete, export, or report on Business B data.



Business-scoped resources include:



\- customers

\- service types

\- estimates

\- estimate items

\- invoices

\- invoice items

\- payments

\- jobs

\- follow-up templates

\- follow-up rules

\- follow-up messages

\- message events

\- audit logs

\- app settings

\- reports



\## 11.4 Validation and Output Safety



Requirements:



\- validate every create/update request with Form Requests

\- escape user-generated output

\- do not render raw HTML from user fields

\- use confirmation modals for destructive actions

\- soft-delete important business records

\- use database transactions for multi-step changes

\- log important actions in audit logs



\---



\# 12. Current Business Handling



Users can belong to one or more businesses through `business\_user`.



For MVP, each demo user can belong to one business, but the architecture should support future multi-business.



Implement a current business resolver.



Suggested behavior:



\- If user belongs to one business, use that business automatically.

\- Store selected business ID in session only after verifying membership.

\- Provide helper/middleware so controllers can access the current business.

\- If user has no business, send them to onboarding.

\- Never allow a user to switch into a business they do not belong to.



Suggested helper methods:



```php

currentBusiness()

currentBusinessId()

userCanAccessBusiness($businessId)

```



Exact implementation may vary.



\---



\# 13. Database Conventions



Use these conventions:



\- Primary keys: `id`

\- Foreign keys: `foreignId`

\- Money values: integer cents, never floats

\- Tax rate: decimal percentage, for example `7.2500`

\- Quantities: decimal, for example `decimal(10, 2)`

\- Timestamps: standard Laravel `created\_at` and `updated\_at`

\- Soft deletes on major business records

\- JSON for metadata/event data/settings values

\- Store timestamps in UTC when possible

\- Display dates/times in the business timezone

\- Use database indexes for `business\_id`, statuses, dates, and foreign keys



Server-side code must calculate totals. Do not trust client-side totals.



\---



\# 14. Database Schema



Create migrations, models, factories, seeders, policies, and relationships for the tables below.



\## 14.1 users



Standard Laravel users table with additions.



Fields:



```text

id

name

email

password

email\_verified\_at

last\_login\_at

remember\_token

created\_at

updated\_at

```



Relationships:



\- belongs to many businesses through `business\_user`

\- has many audit logs



\## 14.2 businesses



Fields:



```text

id

name

trade\_type

phone

email

website

address\_line\_1

address\_line\_2

city

state

zip

timezone

logo\_path

google\_review\_url

facebook\_review\_url

default\_tax\_rate

default\_invoice\_terms

created\_at

updated\_at

```



Relationships:



\- belongs to many users through `business\_user`

\- has many customers

\- has many service types

\- has many estimates

\- has many invoices

\- has many jobs

\- has many follow-up templates

\- has many follow-up rules

\- has many follow-up messages

\- has many audit logs



\## 14.3 business\_user



Fields:



```text

id

business\_id

user\_id

role

created\_at

updated\_at

```



Roles:



```text

owner

manager

staff

```



Constraints:



\- unique pair: `business\_id`, `user\_id`

\- index: `user\_id`

\- index: `business\_id`



\## 14.4 customers



Fields:



```text

id

business\_id

first\_name

last\_name

company\_name

email

phone

address\_line\_1

address\_line\_2

city

state

zip

sms\_consent

email\_consent

sms\_opted\_out\_at

email\_opted\_out\_at

notes

created\_at

updated\_at

deleted\_at

```



Computed/display helpers:



```text

full\_name

display\_name

full\_address

```



Relationships:



\- belongs to business

\- has many estimates

\- has many invoices

\- has many jobs

\- has many follow-up messages



\## 14.5 service\_types



Fields:



```text

id

business\_id

name

category

description

default\_price\_cents

default\_repeat\_months

is\_active

created\_at

updated\_at

```



Relationships:



\- belongs to business

\- has many estimates

\- has many jobs

\- has many follow-up rules



Seed default service types:



```text

General Handyman

Painting

Roofing

Landscaping

Pressure Washing

Asphalt / Sealcoating

Flooring

Gutters

Fencing

HVAC

Deck Repair

Driveway Work

Window and Door

Garage Door

Remodeling

```



\## 14.6 estimates



Fields:



```text

id

business\_id

customer\_id

estimate\_number

service\_type\_id

status

subtotal\_cents

discount\_cents

tax\_rate

tax\_cents

total\_cents

notes

terms

expires\_at

sent\_at

accepted\_at

declined\_at

created\_at

updated\_at

deleted\_at

```



Statuses:



```text

draft

sent

accepted

declined

expired

```



Constraints:



\- unique per business: `business\_id`, `estimate\_number`

\- index: `business\_id`, `status`

\- index: `customer\_id`

\- index: `service\_type\_id`



Relationships:



\- belongs to business

\- belongs to customer

\- belongs to service type

\- has many estimate items

\- has one job

\- has one invoice



\## 14.7 estimate\_items



Fields:



```text

id

business\_id

estimate\_id

description

quantity

unit\_price\_cents

line\_total\_cents

sort\_order

created\_at

updated\_at

```



Constraints:



\- index: `business\_id`

\- index: `estimate\_id`



Relationships:



\- belongs to business

\- belongs to estimate



\## 14.8 invoices



Fields:



```text

id

business\_id

customer\_id

estimate\_id

job\_id

invoice\_number

status

subtotal\_cents

discount\_cents

tax\_rate

tax\_cents

total\_cents

amount\_paid\_cents

balance\_due\_cents

due\_date

sent\_at

paid\_at

created\_at

updated\_at

deleted\_at

```



Statuses:



```text

draft

sent

partially\_paid

paid

overdue

void

```



Constraints:



\- unique per business: `business\_id`, `invoice\_number`

\- index: `business\_id`, `status`

\- index: `customer\_id`

\- index: `estimate\_id`

\- index: `job\_id`

\- index: `due\_date`



Relationships:



\- belongs to business

\- belongs to customer

\- belongs to estimate, nullable

\- belongs to job, nullable

\- has many invoice items

\- has many payments



\## 14.9 invoice\_items



Fields:



```text

id

business\_id

invoice\_id

description

quantity

unit\_price\_cents

line\_total\_cents

sort\_order

created\_at

updated\_at

```



Constraints:



\- index: `business\_id`

\- index: `invoice\_id`



Relationships:



\- belongs to business

\- belongs to invoice



\## 14.10 payments



Fields:



```text

id

business\_id

invoice\_id

amount\_cents

payment\_method

payment\_date

notes

created\_at

updated\_at

```



Payment methods:



```text

cash

check

credit\_card

bank\_transfer

other

```



Constraints:



\- index: `business\_id`

\- index: `invoice\_id`

\- index: `payment\_date`



Relationships:



\- belongs to business

\- belongs to invoice



\## 14.11 jobs



Fields:



```text

id

business\_id

customer\_id

estimate\_id

invoice\_id

service\_type\_id

title

status

scheduled\_date

started\_at

completed\_at

canceled\_at

followups\_scheduled\_at

job\_address

notes

created\_at

updated\_at

deleted\_at

```



Statuses:



```text

scheduled

in\_progress

completed

canceled

```



Constraints:



\- index: `business\_id`, `status`

\- index: `customer\_id`

\- index: `estimate\_id`

\- index: `invoice\_id`

\- index: `service\_type\_id`

\- index: `scheduled\_date`

\- index: `completed\_at`



Relationships:



\- belongs to business

\- belongs to customer

\- belongs to estimate, nullable

\- belongs to invoice, nullable

\- belongs to service type

\- has many follow-up messages



\## 14.12 followup\_templates



Fields:



```text

id

business\_id

name

channel

purpose

subject

body

is\_default

created\_at

updated\_at

```



Channels:



```text

sms

email

```



Purposes:



```text

thank\_you

review\_request

repeat\_service

warranty\_check

seasonal\_reminder

custom

```



Constraints:



\- index: `business\_id`

\- index: `channel`

\- index: `purpose`



Relationships:



\- belongs to business

\- has many follow-up rules

\- has many follow-up messages



\## 14.13 followup\_rules



Fields:



```text

id

business\_id

service\_type\_id

template\_id

trigger\_event

delay\_amount

delay\_unit

channel

purpose

is\_active

created\_at

updated\_at

```



Trigger event for MVP:



```text

job\_completed

```



Delay units:



```text

days

weeks

months

```



Constraints:



\- index: `business\_id`

\- index: `service\_type\_id`

\- index: `template\_id`

\- index: `trigger\_event`

\- index: `is\_active`



Relationships:



\- belongs to business

\- belongs to service type

\- belongs to follow-up template



\## 14.14 followup\_messages



Fields:



```text

id

business\_id

customer\_id

job\_id

template\_id

channel

purpose

status

scheduled\_at

sent\_at

canceled\_at

recipient

subject

body

skip\_reason

created\_at

updated\_at

```



Statuses:



```text

scheduled

simulated\_sent

sent

failed

canceled

skipped

```



Constraints:



\- index: `business\_id`, `status`

\- index: `customer\_id`

\- index: `job\_id`

\- index: `template\_id`

\- index: `channel`

\- index: `purpose`

\- index: `scheduled\_at`



Relationships:



\- belongs to business

\- belongs to customer

\- belongs to job

\- belongs to follow-up template

\- has many message events



\## 14.15 message\_events



Fields:



```text

id

business\_id

followup\_message\_id

event\_type

event\_data

created\_at

```



Event types:



```text

created

simulated\_sent

sent

failed

clicked

replied

opted\_out

skipped

canceled

```



Constraints:



\- index: `business\_id`

\- index: `followup\_message\_id`

\- index: `event\_type`



Relationships:



\- belongs to business

\- belongs to follow-up message



\## 14.16 audit\_logs



Fields:



```text

id

business\_id

user\_id

action

entity\_type

entity\_id

metadata

created\_at

```



Important actions:



```text

login

customer\_created

customer\_updated

customer\_deleted

estimate\_created

estimate\_updated

estimate\_sent

estimate\_accepted

estimate\_declined

invoice\_created

invoice\_sent

payment\_recorded

invoice\_voided

job\_created

job\_started

job\_completed

job\_canceled

followups\_scheduled

message\_simulated

message\_canceled

message\_rescheduled

settings\_updated

```



Constraints:



\- index: `business\_id`

\- index: `user\_id`

\- index: `action`

\- index: `entity\_type`, `entity\_id`

\- index: `created\_at`



Relationships:



\- belongs to business

\- belongs to user, nullable if system action



\## 14.17 app\_settings



Fields:



```text

id

business\_id

key

value

created\_at

updated\_at

```



Constraints:



\- unique per business: `business\_id`, `key`

\- `value` should be JSON or text depending on implementation simplicity



Use for optional settings that do not belong directly on the business row.



\---



\# 15. Relationship Summary



Core relationships:



```text

Business has many Customers

Business has many ServiceTypes

Business has many Estimates

Business has many Invoices

Business has many Jobs

Business has many FollowupTemplates

Business has many FollowupRules

Business has many FollowupMessages



Customer belongs to Business

Customer has many Estimates

Customer has many Invoices

Customer has many Jobs

Customer has many FollowupMessages



Estimate belongs to Business

Estimate belongs to Customer

Estimate belongs to ServiceType

Estimate has many EstimateItems

Estimate has one Job

Estimate has one Invoice



Invoice belongs to Business

Invoice belongs to Customer

Invoice may belong to Estimate

Invoice may belong to Job

Invoice has many InvoiceItems

Invoice has many Payments



Job belongs to Business

Job belongs to Customer

Job may belong to Estimate

Job may belong to Invoice

Job belongs to ServiceType

Job has many FollowupMessages



FollowupRule belongs to Business

FollowupRule belongs to ServiceType

FollowupRule belongs to FollowupTemplate



FollowupMessage belongs to Business

FollowupMessage belongs to Customer

FollowupMessage belongs to Job

FollowupMessage belongs to FollowupTemplate

```



\---



\# 16. Money and Calculation Rules



All money must be stored as integer cents.



Examples:



```text

$100.00 is stored as 10000

$1,250.75 is stored as 125075

```



Never store money values as floats.



Line item calculation:



```text

line\_total\_cents = round(quantity \* unit\_price\_cents)

```



Estimate calculation:



```text

subtotal\_cents = sum(line\_total\_cents)

taxable\_amount\_cents = max(0, subtotal\_cents - discount\_cents)

tax\_cents = round(taxable\_amount\_cents \* tax\_rate / 100)

total\_cents = taxable\_amount\_cents + tax\_cents

```



Invoice calculation follows the same pattern.



Invoice payment calculation:



```text

amount\_paid\_cents = sum(payments.amount\_cents)

balance\_due\_cents = max(0, total\_cents - amount\_paid\_cents)

```



Invoice status after payment:



```text

if balance\_due\_cents == 0:

&#x20;   status = paid

&#x20;   paid\_at = now()

else if amount\_paid\_cents > 0:

&#x20;   status = partially\_paid

else:

&#x20;   keep draft/sent unless void

```



Void invoices should not accept new payments.



Payment validation:



```text

payment amount must be greater than 0

payment amount must be less than or equal to balance\_due\_cents

payment date must be valid

payment method must be one of allowed values

```



Formatting:



\- Format cents as currency in UI.

\- Keep calculations on the server.

\- Client-side calculation is allowed only for display preview.



\---



\# 17. Number Generation Rules



Estimate numbers and invoice numbers must be unique per business.



Recommended formats:



```text

Estimate: EST-1001

Invoice: INV-1001

```



For demo MVP, generate the next number by finding the latest number for the current business and incrementing.



Use a database transaction when creating estimates, invoices, jobs, or converting estimates.



Prevent duplicates:



\- An accepted estimate should not create more than one job.

\- An accepted estimate should not create more than one invoice.

\- If job and invoice already exist, show links to the existing records instead of creating duplicates.



\---



\# 18. Estimate Workflow



\## 18.1 Create Estimate



Required fields:



\- customer

\- service type

\- at least one line item

\- line item description

\- line item quantity

\- line item unit price



Optional fields:



\- discount

\- tax rate

\- notes

\- terms

\- expiration date



On save:



\- validate fields

\- calculate line totals on server

\- calculate estimate totals on server

\- assign estimate number if new

\- save as `draft` unless action says otherwise

\- audit log `estimate\_created`



\## 18.2 Mark Sent



When marked sent:



\- status becomes `sent`

\- `sent\_at` is set

\- audit log `estimate\_sent`

\- in demo mode, optionally create a simulated email/message record only if implemented simply



\## 18.3 Mark Accepted



When marked accepted:



\- status becomes `accepted`

\- `accepted\_at` is set

\- audit log `estimate\_accepted`

\- show action to create job and invoice



\## 18.4 Mark Declined



When marked declined:



\- status becomes `declined`

\- `declined\_at` is set

\- audit log `estimate\_declined`



\## 18.5 Create Job and Invoice From Accepted Estimate



Allowed only when:



\- estimate belongs to current business

\- estimate status is `accepted`

\- estimate does not already have linked job and invoice



Process:



1\. Start database transaction.

2\. Create job linked to estimate.

3\. Create invoice linked to estimate and job.

4\. Copy estimate line items to invoice items.

5\. Calculate invoice totals.

6\. Save links both ways where needed.

7\. Commit transaction.

8\. Audit log `job\_created` and `invoice\_created`.



Default job title:



```text

{{service\_type.name}} for {{customer.display\_name}}

```



Default due date:



```text

today + 14 days

```



or use business default terms if implemented.



\---



\# 19. Invoice Workflow



\## 19.1 Create Invoice



Invoices can be created from accepted estimates or manually.



For manual invoices:



\- require customer

\- require at least one line item

\- service type is optional unless linked through a job

\- calculate totals on server

\- assign invoice number

\- default status `draft`



\## 19.2 Mark Sent



When marked sent:



\- status becomes `sent`

\- `sent\_at` is set

\- audit log `invoice\_sent`



\## 19.3 Record Payment



Payment flow:



1\. Validate invoice belongs to current business.

2\. Validate invoice is not void.

3\. Validate payment amount.

4\. Create payment.

5\. Recalculate amount paid.

6\. Recalculate balance due.

7\. Update invoice status.

8\. Audit log `payment\_recorded`.



\## 19.4 Void Invoice



When voided:



\- status becomes `void`

\- balance due can remain for history, but reports should exclude void invoices from unpaid totals

\- no further payments allowed

\- audit log `invoice\_voided`



\---



\# 20. Job Workflow



\## 20.1 Create Job



Jobs are usually created from accepted estimates.



Fields:



\- customer

\- service type

\- title

\- status

\- scheduled date

\- job address

\- notes



Default status:



```text

scheduled

```



\## 20.2 Start Job



When started:



\- status becomes `in\_progress`

\- `started\_at` is set if empty

\- audit log `job\_started`



\## 20.3 Complete Job



When completed:



\- status becomes `completed`

\- `completed\_at` is set if empty

\- follow-up automation runs

\- audit log `job\_completed`



Follow-ups should only be scheduled once per job. Use `followups\_scheduled\_at` to prevent duplicates.



\## 20.4 Cancel Job



When canceled:



\- status becomes `canceled`

\- `canceled\_at` is set

\- no follow-ups are scheduled

\- audit log `job\_canceled`



\---



\# 21. Follow-Up Automation



The automation engine is the heart of TradeLoop.



\## 21.1 Trigger



Trigger event:



```text

job\_completed

```



When a job changes to `completed`, the app must:



1\. Set `completed\_at` if missing.

2\. Check `followups\_scheduled\_at`.

3\. If follow-ups were already scheduled, do not duplicate.

4\. Find active follow-up rules for the job’s service type.

5\. Render each rule’s template.

6\. Create follow-up messages.

7\. Respect customer consent and channel availability.

8\. Log message events.

9\. Set `followups\_scheduled\_at`.

10\. Add audit log `followups\_scheduled`.



\## 21.2 Template Variables



Support these variables:



```text

{{business\_name}}

{{customer\_first\_name}}

{{customer\_full\_name}}

{{service\_name}}

{{job\_completed\_date}}

{{google\_review\_url}}

{{facebook\_review\_url}}

{{business\_phone}}

```



Optional recommended variables:



```text

{{business\_email}}

{{customer\_company\_name}}

{{job\_title}}

{{job\_address}}

```



If a variable is missing, render gracefully. Do not show broken placeholder text to customers if possible. Empty strings are acceptable.



\## 21.3 Consent Rules



For SMS:



\- customer must have phone

\- customer must have `sms\_consent = true`

\- customer must not have `sms\_opted\_out\_at`



For email:



\- customer must have email

\- customer must have `email\_consent = true`

\- customer must not have `email\_opted\_out\_at`



If consent or recipient is missing:



\- create a follow-up message with status `skipped`

\- set `skip\_reason`

\- create a message event with event type `skipped`



Do not silently ignore skipped messages. The contractor should be able to see why a message was skipped.



\## 21.4 Scheduled Date Calculation



Rules use:



```text

delay\_amount

delay\_unit

```



Examples:



```text

1 days

3 days

6 months

12 months

24 months

```



Scheduled date:



```text

scheduled\_at = job.completed\_at + delay

```



Use business timezone for display. Store timestamps consistently.



\## 21.5 Message Creation



For each active matching follow-up rule:



\- copy rendered template subject/body into follow-up message

\- store recipient at time of scheduling

\- set status `scheduled` unless skipped

\- set purpose from rule

\- set channel from rule

\- link to business, customer, job, and template

\- create message event `created`



\## 21.6 Processing Due Follow-Ups



Create command:



```bash

php artisan followups:process-due

```



Behavior:



\- find messages where status is `scheduled`

\- `scheduled\_at <= now()`

\- if `DEMO\_MODE=true`, set status `simulated\_sent`

\- set `sent\_at`

\- create message event `simulated\_sent`

\- audit log `message\_simulated`



In demo mode, never set status `sent`.



\## 21.7 Send Now



In the UI, “Send Now” should:



\- validate current business access

\- check message is scheduled

\- re-check consent

\- in demo mode, mark as `simulated\_sent`

\- set `sent\_at`

\- create message event

\- audit log



\## 21.8 Cancel and Reschedule



Cancel:



\- status becomes `canceled`

\- `canceled\_at` is set

\- message event `canceled`

\- audit log `message\_canceled`



Reschedule:



\- update `scheduled\_at`

\- keep status `scheduled`

\- audit log `message\_rescheduled`



\---



\# 22. Default Follow-Up Templates



Seed SMS and email templates for each purpose.



\## 22.1 SMS Thank You



Name:



```text

SMS Thank You

```



Purpose:



```text

thank\_you

```



Body:



```text

Hi {{customer\_first\_name}}, thanks again for choosing {{business\_name}} for your {{service\_name}}. We appreciate your business.

```



\## 22.2 SMS Review Request



Name:



```text

SMS Review Request

```



Purpose:



```text

review\_request

```



Body:



```text

Hi {{customer\_first\_name}}, thanks again for choosing {{business\_name}} for your {{service\_name}}. If you were happy with the work, would you mind leaving us a quick review? {{google\_review\_url}}

```



\## 22.3 SMS Repeat Service Reminder



Name:



```text

SMS Repeat Service Reminder

```



Purpose:



```text

repeat\_service

```



Body:



```text

Hi {{customer\_first\_name}}, this is {{business\_name}}. It has been a while since we completed your {{service\_name}}. Would you like us to schedule a quick check-in or maintenance visit?

```



\## 22.4 SMS Warranty Check



Name:



```text

SMS Warranty Check

```



Purpose:



```text

warranty\_check

```



Body:



```text

Hi {{customer\_first\_name}}, this is {{business\_name}} checking in on your {{service\_name}}. Is everything still looking good?

```



\## 22.5 SMS Seasonal Reminder



Name:



```text

SMS Seasonal Reminder

```



Purpose:



```text

seasonal\_reminder

```



Body:



```text

Hi {{customer\_first\_name}}, this is {{business\_name}}. We are scheduling seasonal {{service\_name}} visits now. Would you like to get on the calendar?

```



\## 22.6 Email Review Request



Subject:



```text

Thanks again from {{business\_name}}

```



Body:



```text

Hi {{customer\_first\_name}},



Thank you again for choosing {{business\_name}} for your {{service\_name}}.



If you were happy with the work, would you mind leaving us a quick review? It really helps small businesses like ours.



Google review link:

{{google\_review\_url}}



Thanks,

{{business\_name}}

{{business\_phone}}

```



Email templates can be simple plain text for demo.



\---



\# 23. Default Follow-Up Rule Seeds



Seed follow-up rules by service type.



\## 23.1 General Handyman



```text

Day 1: thank\_you

Day 3: review\_request

Month 6: warranty\_check

Month 12: repeat\_service

```



\## 23.2 Asphalt / Sealcoating



```text

Day 1: thank\_you

Day 3: review\_request

Month 11: repeat\_service

Month 24: repeat\_service

```



\## 23.3 Painting



```text

Day 1: thank\_you

Day 4: review\_request

Month 12: warranty\_check

Month 24: repeat\_service

```



\## 23.4 Pressure Washing



```text

Day 1: thank\_you

Day 3: review\_request

Month 6: repeat\_service

Month 12: seasonal\_reminder

```



\## 23.5 Roofing



```text

Day 1: thank\_you

Day 5: review\_request

Month 11: warranty\_check

Month 24: repeat\_service

```



\## 23.6 Landscaping



```text

Day 1: thank\_you

Day 3: review\_request

Month 3: seasonal\_reminder

Month 6: repeat\_service

```



\## 23.7 Gutters



```text

Day 1: thank\_you

Day 3: review\_request

Month 6: repeat\_service

Month 12: seasonal\_reminder

```



\## 23.8 HVAC



```text

Day 1: thank\_you

Day 4: review\_request

Month 6: repeat\_service

Month 12: seasonal\_reminder

```



\## 23.9 Deck Repair



```text

Day 1: thank\_you

Day 3: review\_request

Month 12: warranty\_check

Month 24: repeat\_service

```



\## 23.10 Fencing



```text

Day 1: thank\_you

Day 3: review\_request

Month 12: warranty\_check

Month 24: repeat\_service

```



For service types without custom rules, seed the General Handyman rule set.



\---



\# 24. Demo Mode



The first version is a full working demo.



Environment flag:



```env

DEMO\_MODE=true

```



\## 24.1 Demo Behavior



When demo mode is enabled:



\- login page shows “Try Demo”

\- Try Demo logs in the seeded demo user

\- no real email sends

\- no real SMS sends

\- follow-up sending is simulated

\- app shows a demo banner

\- destructive demo actions are allowed because demo data can be reset

\- demo reset command exists



\## 24.2 Demo Login



Seed demo credentials:



```text

Email: demo@tradeloop.test

Password: password

Business: Smith Home Services

Trade Type: General Handyman

Role: owner

```



Optional staff demo:



```text

Email: staff@tradeloop.test

Password: password

Business: Smith Home Services

Role: staff

```



\## 24.3 Demo Business Profile



Seed business:



```text

Name: Smith Home Services

Trade Type: General Handyman

Phone: (555) 201-8833

Email: hello@smithhomeservices.test

Website: https://smithhomeservices.test

City: Columbus

State: OH

Timezone: America/New\_York

Google Review URL: https://example.com/google-review

Facebook Review URL: https://example.com/facebook-review

Default Tax Rate: 7.50

Default Invoice Terms: Payment due within 14 days.

```



\## 24.4 Demo Data Requirements



Seed at least:



```text

25 customers

12 estimates

9 invoices

7 completed jobs

20 scheduled follow-ups

5 simulated review requests

3 unpaid invoices

multiple payments

multiple service types

multiple repeat-service opportunities

```



Demo data should produce interesting dashboard/report numbers.



Include a mix of:



\- paid invoices

\- partially paid invoices

\- unpaid invoices

\- overdue invoices

\- open estimates

\- accepted estimates

\- declined estimates

\- completed jobs

\- upcoming follow-ups

\- simulated sent review requests

\- skipped messages due to missing consent



\## 24.5 Demo Reset Command



Create command:



```bash

php artisan demo:reset

```



Behavior:



\- locate demo business/user

\- delete demo business data in safe order

\- reseed demo business

\- reseed customers

\- reseed service types

\- reseed templates

\- reseed rules

\- reseed estimates

\- reseed invoices

\- reseed payments

\- reseed jobs

\- reseed follow-up messages

\- output summary counts



The command should not wipe non-demo businesses.



Mark demo business clearly, either by email/domain or an app setting.



\---



\# 25. Main Screens



The UI must be clean, mobile-friendly, and simple.



Use Tailwind CSS.



Use a consistent authenticated layout with navigation:



```text

Dashboard

Customers

Estimates

Invoices

Jobs

Follow-Ups

Reports

Settings

```



Staff users should not see navigation items they cannot access.



Use status badges, simple tables, search fields, filter dropdowns, and clear empty states.



\## 25.1 Login Screen



Fields/actions:



\- email

\- password

\- remember me

\- forgot password link if available

\- login button

\- Try Demo button when `DEMO\_MODE=true`



Tone:



```text

Welcome back to TradeLoop.

Finish the job. TradeLoop handles the follow-up.

```



\## 25.2 Onboarding / Business Setup



If an authenticated user has no business, show onboarding.



Fields:



\- business name

\- trade type

\- phone

\- email

\- website

\- address line 1

\- address line 2

\- city

\- state

\- zip

\- timezone

\- Google review URL

\- Facebook review URL

\- default tax rate

\- default invoice terms



After onboarding:



\- create business

\- attach user as owner

\- seed default service types

\- seed default message templates

\- seed default follow-up rules

\- redirect to dashboard



\## 25.3 Dashboard



Dashboard cards:



\- Revenue This Month

\- Open Estimate Value

\- Accepted Estimate Value

\- Unpaid Invoices

\- Overdue Invoices

\- Jobs Completed

\- Average Job Value

\- Review Requests Sent

\- Follow-Ups Due Today

\- Repeat Revenue Opportunity



Dashboard sections:



\- Quick Actions

\- Recent Customers

\- Recent Jobs

\- Upcoming Follow-Ups

\- Unpaid Invoices

\- Repeat Revenue Pipeline



Quick actions:



\- Add Customer

\- Create Estimate

\- Create Invoice

\- View Follow-Ups



\## 25.4 Customers List



Features:



\- search

\- pagination

\- create button

\- table/list view responsive to mobile



Columns/cards:



\- name

\- phone

\- email

\- city/state

\- last job

\- total revenue

\- follow-ups due



Actions:



\- view

\- edit

\- delete



\## 25.5 Customer Detail



Sections:



\- contact information

\- address

\- consent status

\- notes

\- estimates

\- invoices

\- jobs

\- payments

\- scheduled follow-ups

\- message history



Actions:



\- edit customer

\- create estimate for customer

\- create invoice for customer

\- create job for customer



\## 25.6 Estimate List



Features:



\- filter by status

\- search by customer/name/number

\- create button

\- total value by status if simple



Columns:



\- estimate number

\- customer

\- service type

\- status

\- total

\- expires

\- created date



\## 25.7 Estimate Builder / Detail



Fields:



\- customer

\- service type

\- line items

\- quantity

\- unit price

\- discount

\- tax rate

\- notes

\- terms

\- expiration date



Actions:



\- save draft

\- mark sent

\- mark accepted

\- mark declined

\- print view

\- create job and invoice if accepted



Important:



\- totals must calculate on server

\- client may show preview

\- accepted estimate cannot be edited in a way that breaks linked invoice/job unless explicit future feature



\## 25.8 Invoice List



Features:



\- filter by status

\- search by customer/number

\- unpaid and overdue highlights



Columns:



\- invoice number

\- customer

\- status

\- total

\- paid

\- balance due

\- due date



\## 25.9 Invoice Detail



Sections:



\- invoice header

\- customer info

\- line items

\- totals

\- payments

\- status history if simple



Actions:



\- mark sent

\- record payment

\- void

\- print view



\## 25.10 Jobs List



Features:



\- filter by status

\- filter by scheduled date

\- search



Columns:



\- title

\- customer

\- service type

\- status

\- scheduled date

\- completed date



\## 25.11 Job Detail



Sections:



\- job info

\- customer info

\- estimate link

\- invoice link

\- scheduled/completed details

\- notes

\- follow-up messages



Actions:



\- schedule

\- start

\- complete

\- cancel



Completing a job must visibly show that follow-ups were scheduled.



Example success message:



```text

Job completed. TradeLoop scheduled 4 follow-ups for this customer.

```



\## 25.12 Follow-Ups



Tabs:



\- Due Today

\- Upcoming

\- Sent

\- Skipped

\- Canceled



Columns:



\- scheduled date

\- customer

\- job

\- channel

\- purpose

\- status

\- recipient



Actions:



\- preview

\- send now

\- reschedule

\- cancel



In demo mode, clearly label simulated sending.



Example:



```text

Demo mode: messages are simulated and will not be sent to customers.

```



\## 25.13 Reports



Filters:



\- Today

\- This Week

\- This Month

\- Last Month

\- This Year

\- Custom Date Range

\- Service Type



Reports:



\- Sales Summary

\- Revenue by Month

\- Revenue by Service Type

\- Invoice Aging

\- Estimate Win Rate

\- Repeat Revenue Opportunity



\## 25.14 Settings



Sections:



\- Business Profile

\- Review Links

\- Service Types

\- Message Templates

\- Follow-Up Rules

\- Team / Users

\- Security

\- Demo Mode Notice



For MVP, Team / Users can be basic.



\---



\# 26. Reporting Requirements



All reporting queries must be business-scoped.



Use a `ReportService` or similar.



\## 26.1 Date Range Handling



Reports must support:



\- today

\- this week

\- this month

\- last month

\- this year

\- custom range



Use business timezone for interpreting dates when possible.



\## 26.2 Revenue This Month



Definition:



```text

sum payments.amount\_cents where payment\_date is inside current month

```



\## 26.3 Open Estimate Value



Definition:



```text

sum estimates.total\_cents where status in draft, sent

```



\## 26.4 Accepted Estimate Value



Definition:



```text

sum estimates.total\_cents where status = accepted

```



On reports page, apply selected date range.



\## 26.5 Unpaid Invoices



Definition:



```text

sum invoices.balance\_due\_cents

where balance\_due\_cents > 0

and status not in paid, void

```



\## 26.6 Overdue Invoices



Definition:



```text

sum invoices.balance\_due\_cents

where due\_date < today

and balance\_due\_cents > 0

and status not in paid, void

```



\## 26.7 Jobs Completed



Definition:



```text

count jobs

where status = completed

and completed\_at inside selected period

```



\## 26.8 Average Job Value



Preferred definition:



```text

paid revenue linked to completed jobs / count of completed paid jobs

```



Fallback:



```text

paid revenue / completed jobs

```



Avoid divide-by-zero.



\## 26.9 Review Requests Sent



Definition:



```text

count followup\_messages

where purpose = review\_request

and status in simulated\_sent, sent

and sent\_at inside selected period

```



For demo, mostly `simulated\_sent`.



\## 26.10 Follow-Ups Due Today



Definition:



```text

count followup\_messages

where status = scheduled

and scheduled\_at is today in business timezone

```



\## 26.11 Estimate Win Rate



Definition:



```text

accepted estimates / (accepted estimates + declined estimates)

```



Show as percentage.



If denominator is zero, show `0%` or `No closed estimates yet`.



\## 26.12 Invoice Aging



Group unpaid invoice balances into:



\- Current

\- 1–30 days overdue

\- 31–60 days overdue

\- 61–90 days overdue

\- 90+ days overdue



Use due date and current date.



\## 26.13 Revenue by Service Type



Use paid payments linked through invoices/jobs/service types.



Recommended resolution order:



1\. invoice -> job -> service\_type

2\. invoice -> estimate -> service\_type

3\. if missing, group as `Uncategorized`



\## 26.14 Revenue by Month



Group payments by month using payment date.



\## 26.15 Repeat Revenue Opportunity



Use scheduled follow-up messages where purpose is one of:



```text

repeat\_service

seasonal\_reminder

warranty\_check

```



And scheduled date is within:



\- next 30 days

\- next 60 days

\- next 90 days



Estimated opportunity:



```text

service\_types.default\_price\_cents

```



If default price is missing or zero:



```text

average paid job value for that service type

```



If still unavailable:



```text

business average paid job value

```



Show:



```text

customers due

estimated opportunity

top upcoming opportunities

```



Example copy:



```text

38 past customers are due for repeat-service follow-up in the next 90 days.

Estimated opportunity: $18,750.

```



This is a key selling feature.



\---



\# 27. Routes and Controllers



Exact names may vary, but the app should include these areas.



\## 27.1 Public/Auth Routes



```text

GET /login

POST /login

POST /logout

POST /demo-login

GET /forgot-password

POST /forgot-password

```



Only show demo login when `DEMO\_MODE=true`.



\## 27.2 Authenticated Routes



```text

GET /dashboard

GET /onboarding

POST /onboarding

```



\## 27.3 Customers



```text

GET /customers

GET /customers/create

POST /customers

GET /customers/{customer}

GET /customers/{customer}/edit

PUT/PATCH /customers/{customer}

DELETE /customers/{customer}

```



\## 27.4 Estimates



```text

GET /estimates

GET /estimates/create

POST /estimates

GET /estimates/{estimate}

GET /estimates/{estimate}/edit

PUT/PATCH /estimates/{estimate}

DELETE /estimates/{estimate}

POST /estimates/{estimate}/mark-sent

POST /estimates/{estimate}/accept

POST /estimates/{estimate}/decline

POST /estimates/{estimate}/create-job-and-invoice

GET /estimates/{estimate}/print

```



\## 27.5 Invoices



```text

GET /invoices

GET /invoices/create

POST /invoices

GET /invoices/{invoice}

GET /invoices/{invoice}/edit

PUT/PATCH /invoices/{invoice}

DELETE /invoices/{invoice}

POST /invoices/{invoice}/mark-sent

POST /invoices/{invoice}/payments

POST /invoices/{invoice}/void

GET /invoices/{invoice}/print

```



\## 27.6 Jobs



```text

GET /jobs

GET /jobs/create

POST /jobs

GET /jobs/{job}

GET /jobs/{job}/edit

PUT/PATCH /jobs/{job}

DELETE /jobs/{job}

POST /jobs/{job}/start

POST /jobs/{job}/complete

POST /jobs/{job}/cancel

```



\## 27.7 Follow-Ups



```text

GET /follow-ups

GET /follow-ups/{followupMessage}

POST /follow-ups/{followupMessage}/send-now

POST /follow-ups/{followupMessage}/cancel

POST /follow-ups/{followupMessage}/reschedule

```



\## 27.8 Reports



```text

GET /reports

```



\## 27.9 Settings



```text

GET /settings

GET /settings/business

PUT/PATCH /settings/business

GET /settings/service-types

POST /settings/service-types

PUT/PATCH /settings/service-types/{serviceType}

DELETE /settings/service-types/{serviceType}

GET /settings/message-templates

POST /settings/message-templates

PUT/PATCH /settings/message-templates/{template}

DELETE /settings/message-templates/{template}

GET /settings/follow-up-rules

POST /settings/follow-up-rules

PUT/PATCH /settings/follow-up-rules/{rule}

DELETE /settings/follow-up-rules/{rule}

GET /settings/team

```



For MVP, some settings pages can be simple but should exist.



\---



\# 28. Validation Rules



Use Form Requests.



\## 28.1 Customer Validation



Required:



\- first name or company name

\- valid email if provided

\- valid phone if provided



Booleans:



\- sms\_consent

\- email\_consent



Notes can be nullable.



\## 28.2 Business Validation



Required:



\- name

\- trade type

\- timezone



Optional:



\- phone

\- email

\- website

\- address

\- review URLs

\- tax rate

\- invoice terms



Tax rate:



\- numeric

\- minimum 0

\- reasonable maximum such as 25



\## 28.3 Estimate Validation



Required:



\- customer ID belonging to current business

\- service type ID belonging to current business

\- at least one line item

\- line item description

\- line item quantity greater than 0

\- line item unit price greater than or equal to 0



Discount:



\- cannot be negative

\- cannot exceed subtotal unless handled safely



Tax rate:



\- numeric

\- minimum 0

\- reasonable maximum



\## 28.4 Invoice Validation



Same as estimate validation for manual invoice.



Payment validation:



\- amount greater than 0

\- amount less than or equal to balance due

\- method in allowed values

\- date required



\## 28.5 Job Validation



Required:



\- customer ID belonging to current business

\- service type ID belonging to current business

\- title



Optional:



\- scheduled date

\- job address

\- notes



\## 28.6 Follow-Up Validation



Reschedule:



\- scheduled date required

\- scheduled date must be valid



Template:



\- name required

\- channel required

\- purpose required

\- body required

\- subject required for email, optional for SMS



Rule:



\- service type required

\- template required

\- trigger event required

\- delay amount required

\- delay unit required

\- channel required

\- purpose required



\---



\# 29. UI Design Direction



Brand name:



```text

TradeLoop

```



Tagline:



```text

Finish the job. TradeLoop handles the follow-up.

```



Personality:



\- practical

\- reliable

\- plain-spoken

\- contractor-friendly

\- not corporate

\- not overly playful



Suggested visual direction:



\- clean white/neutral background

\- dark text

\- subtle borders

\- clear status badges

\- one primary accent color

\- large touch-friendly buttons

\- mobile-friendly tables that collapse into cards



Important UI rules:



\- Use simple labels.

\- Avoid dense forms.

\- Use helpful empty states.

\- Use confirmation prompts.

\- Show success messages after actions.

\- Keep navigation obvious.

\- Make quick actions prominent.

\- Show demo banner in demo mode.



Example empty states:



```text

No follow-ups due today.

Complete a job to automatically schedule follow-ups.

You have no unpaid invoices.

Add your first customer to create an estimate.

```



Example status badge labels:



```text

Draft

Sent

Accepted

Declined

Paid

Partially Paid

Overdue

Scheduled

Completed

Simulated Sent

Skipped

```



\---



\# 30. Inertia React Page Structure



Recommended page paths:



```text

resources/js/Pages/Auth/Login.jsx

resources/js/Pages/Onboarding/Index.jsx

resources/js/Pages/Dashboard/Index.jsx

resources/js/Pages/Customers/Index.jsx

resources/js/Pages/Customers/Create.jsx

resources/js/Pages/Customers/Edit.jsx

resources/js/Pages/Customers/Show.jsx

resources/js/Pages/Estimates/Index.jsx

resources/js/Pages/Estimates/Create.jsx

resources/js/Pages/Estimates/Edit.jsx

resources/js/Pages/Estimates/Show.jsx

resources/js/Pages/Invoices/Index.jsx

resources/js/Pages/Invoices/Create.jsx

resources/js/Pages/Invoices/Edit.jsx

resources/js/Pages/Invoices/Show.jsx

resources/js/Pages/Jobs/Index.jsx

resources/js/Pages/Jobs/Create.jsx

resources/js/Pages/Jobs/Edit.jsx

resources/js/Pages/Jobs/Show.jsx

resources/js/Pages/FollowUps/Index.jsx

resources/js/Pages/FollowUps/Show.jsx

resources/js/Pages/Reports/Index.jsx

resources/js/Pages/Settings/Index.jsx

resources/js/Pages/Settings/Business.jsx

resources/js/Pages/Settings/ServiceTypes.jsx

resources/js/Pages/Settings/MessageTemplates.jsx

resources/js/Pages/Settings/FollowupRules.jsx

resources/js/Pages/Settings/Team.jsx

```



Recommended reusable components:



```text

AppLayout

GuestLayout

NavLink

MobileNav

PageHeader

MetricCard

StatusBadge

MoneyText

DateText

EmptyState

ConfirmButton

PrimaryButton

SecondaryButton

DangerButton

TextInput

SelectInput

TextareaInput

CheckboxInput

LineItemEditor

Pagination

SearchFilter

DemoBanner

```



\---



\# 31. Commands and Scheduler



\## 31.1 demo:reset



Command:



```bash

php artisan demo:reset

```



Purpose:



\- safely reseed demo data



\## 31.2 followups:process-due



Command:



```bash

php artisan followups:process-due

```



Purpose:



\- process due scheduled messages

\- simulate sending in demo mode



\## 31.3 Optional invoices:mark-overdue



Command:



```bash

php artisan invoices:mark-overdue

```



Purpose:



\- mark unpaid past-due invoices as overdue



This is optional because reports can calculate overdue dynamically.



\## 31.4 Scheduler



Register:



```php

$schedule->command('followups:process-due')->everyMinute();

```



Optional:



```php

$schedule->command('invoices:mark-overdue')->daily();

```



\---



\# 32. Audit Logging



Create a simple `AuditLogger` service.



Audit logs should include:



\- business ID

\- user ID if available

\- action

\- entity type

\- entity ID

\- metadata JSON

\- created date



Use for important business changes only. Do not over-log every page view.



Important audit examples:



\- demo login

\- customer created

\- estimate accepted

\- invoice created

\- payment recorded

\- job completed

\- follow-ups scheduled

\- message simulated

\- settings updated



\---



\# 33. Print Views



For demo MVP, use print-friendly HTML pages instead of PDF generation.



Estimate print view:



```text

GET /estimates/{estimate}/print

```



Invoice print view:



```text

GET /invoices/{invoice}/print

```



Print view requirements:



\- business name/contact info

\- customer name/contact info

\- estimate/invoice number

\- issue date

\- due/expiration date

\- line items

\- subtotal

\- discount

\- tax

\- total

\- notes/terms

\- browser print-friendly CSS



No PDF library is required in MVP.



\---



\# 34. Testing Requirements



Create automated tests.



Use SQLite in-memory for tests if simplest.



Tests should be run with:



```bash

php artisan test

```



\## 34.1 Auth Tests



Required tests:



\- user can log in

\- invalid login is rejected

\- unauthenticated user cannot access dashboard

\- user can log out

\- demo login works when `DEMO\_MODE=true`

\- demo login is unavailable or rejected when `DEMO\_MODE=false`



\## 34.2 Business Isolation Tests



Required tests:



\- Business A user cannot view Business B customers

\- Business A user cannot view Business B estimates

\- Business A user cannot view Business B invoices

\- Business A user cannot view Business B jobs

\- Business A user cannot view Business B follow-ups

\- Business A user cannot view Business B reports

\- Business A user cannot update Business B settings

\- Business A user cannot access Business B child records through direct IDs



\## 34.3 Role Tests



Required tests:



\- owner can access settings

\- manager can access reports

\- staff cannot access reports

\- staff cannot access invoices

\- staff can access allowed jobs/customers routes



\## 34.4 Customer Tests



Required tests:



\- create customer

\- update customer

\- soft-delete customer

\- search customer

\- consent fields save correctly

\- opted-out customer is respected by follow-ups



\## 34.5 Estimate Tests



Required tests:



\- create estimate

\- line item totals calculate correctly

\- tax calculates correctly

\- discount calculates correctly

\- estimate total calculates correctly

\- mark estimate sent

\- mark estimate accepted

\- mark estimate declined

\- accepted estimate can create job and invoice

\- accepted estimate cannot create duplicate job and invoice

\- cross-business service type/customer cannot be used



\## 34.6 Invoice Tests



Required tests:



\- invoice copies estimate items correctly

\- create manual invoice

\- record partial payment

\- record full payment

\- full payment marks invoice paid

\- payment cannot exceed balance

\- void invoice cannot accept payment

\- overdue invoice appears in aging report



\## 34.7 Job Tests



Required tests:



\- create job

\- start job

\- complete job

\- completed job schedules follow-ups

\- completing job twice does not duplicate follow-ups

\- canceled job does not schedule follow-ups

\- job completion sets completed\_at



\## 34.8 Follow-Up Tests



Required tests:



\- follow-up rules generate scheduled messages

\- template variables render correctly

\- messages respect SMS consent

\- messages respect email consent

\- missing phone creates skipped SMS message

\- missing email creates skipped email message

\- no consent creates skipped message

\- demo send marks message `simulated\_sent`

\- message events are recorded

\- send now works in demo mode

\- cancel follow-up works

\- reschedule follow-up works



\## 34.9 Report Tests



Required tests:



\- revenue total is accurate

\- open estimate total is accurate

\- accepted estimate total is accurate

\- unpaid invoice total is accurate

\- overdue invoice total is accurate

\- estimate win rate is accurate

\- revenue by service type is accurate

\- invoice aging buckets are accurate

\- repeat opportunity report is accurate



\---



\# 35. Seeders and Factories



Create factories for:



\- User

\- Business

\- Customer

\- ServiceType

\- Estimate

\- EstimateItem

\- Invoice

\- InvoiceItem

\- Payment

\- Job

\- FollowupTemplate

\- FollowupRule

\- FollowupMessage



Create seeders:



```text

DatabaseSeeder

DemoSeeder

ServiceTypeSeeder

FollowupTemplateSeeder

FollowupRuleSeeder

```



Demo seeders should create realistic data.



Suggested demo customers:



\- homeowners with first and last names

\- some customers with email and phone

\- some customers missing email

\- some customers with no SMS consent

\- some customers opted out



Suggested demo service types:



\- Handyman Repair

\- Deck Repair

\- Gutter Cleaning

\- Pressure Washing

\- Interior Painting

\- Fence Repair

\- Driveway Work

\- Landscaping Cleanup



Suggested invoice states:



\- paid

\- partially paid

\- unpaid

\- overdue



Suggested follow-up states:



\- scheduled

\- simulated\_sent

\- skipped

\- canceled



\---



\# 36. README Requirements



Create or update `README.md`.



README must include:



\- project summary

\- stack

\- local setup

\- environment setup

\- database setup

\- demo data setup

\- commands

\- test instructions

\- Cloudways deployment notes

\- demo login credentials

\- reminder that demo mode does not send SMS/email



Suggested setup commands:



```bash

composer install

npm install

cp .env.example .env

php artisan key:generate

php artisan migrate --seed

php artisan demo:reset

npm run build

php artisan test

```



Development commands:



```bash

npm run dev

php artisan serve

php artisan queue:work

php artisan schedule:work

```



Production/demo commands:



```bash

composer install --no-dev --optimize-autoloader

npm install

npm run build

php artisan migrate --force

php artisan config:cache

php artisan route:cache

php artisan view:cache

```



Cron note:



```bash

\* \* \* \* \* cd /path/to/tradeloop \&\& php artisan schedule:run >> /dev/null 2>\&1

```



\---



\# 37. Build Order



Codex should build in this order:



1\. Inspect project root.

2\. Create Laravel app if one does not exist.

3\. Install/configure React + Inertia + Tailwind auth starter.

4\. Configure environment example.

5\. Build authentication and demo login.

6\. Build business/user/role structure.

7\. Build current business resolver and middleware.

8\. Create migrations.

9\. Create models and relationships.

10\. Create policies.

11\. Create factories and seeders.

12\. Build app layout and navigation.

13\. Build dashboard shell.

14\. Build customers.

15\. Build service types.

16\. Build estimates and estimate calculations.

17\. Build accepted estimate to job/invoice conversion.

18\. Build invoices and payment recording.

19\. Build jobs and job actions.

20\. Build follow-up templates.

21\. Build follow-up rules.

22\. Build job completion follow-up automation.

23\. Build simulated outbox and due-message processing.

24\. Build reports.

25\. Build settings.

26\. Build print views.

27\. Add demo reset command.

28\. Add tests.

29\. Run tests and fix failures.

30\. Polish UI and empty states.

31\. Update README.



\---



\# 38. MVP Acceptance Criteria



The MVP demo is complete only when all items below are true.



1\. A contractor can log in.

2\. A contractor can click Try Demo and enter a seeded demo account.

3\. A contractor can view a demo-mode banner.

4\. A contractor can create and edit customers.

5\. Customer SMS/email consent is stored.

6\. A contractor can create an estimate with line items.

7\. Estimate totals calculate correctly on the server.

8\. A contractor can mark an estimate sent.

9\. A contractor can mark an estimate accepted.

10\. An accepted estimate can create a linked job and invoice.

11\. Duplicate job/invoice creation from the same estimate is prevented.

12\. A contractor can record invoice payments.

13\. Invoice balances update correctly.

14\. Full payment marks invoice paid.

15\. A contractor can mark a job complete.

16\. Completing a job automatically schedules follow-ups.

17\. Follow-ups are based on the service type.

18\. Follow-ups respect customer consent.

19\. Skipped messages are visible with reasons.

20\. Demo sending marks messages as `simulated\_sent`.

21\. No real SMS or email is sent.

22\. Contractor can view scheduled, sent, skipped, and canceled follow-ups.

23\. Dashboard metrics update after actions.

24\. Reports show accurate sales numbers.

25\. Repeat revenue opportunity is visible.

26\. Business data is isolated by account.

27\. Role restrictions work.

28\. Demo data can be reset with `php artisan demo:reset`.

29\. Due follow-ups can be processed with `php artisan followups:process-due`.

30\. App works on desktop and mobile browser.

31\. Tests pass.

32\. README contains setup and deployment instructions.



\---



\# 39. Done Means Done



The project is not done until:



\- migrations run cleanly

\- seeders run cleanly

\- demo reset works

\- demo login works

\- no real SMS/email sends

\- all major flows work from the browser

\- business isolation tests exist and pass

\- follow-up automation tests exist and pass

\- reports are business-scoped and accurate

\- README is updated

\- app can be built with `npm run build`

\- `php artisan test` passes



\---



\# 40. Future Roadmap



Do not build these now, but leave architecture clean enough to support them later.



Potential future features:



\- real Twilio SMS

\- real email delivery through Resend, Postmark, Mailgun, or SMTP

\- Stripe SaaS billing

\- online invoice payments

\- customer portal

\- quote approval and e-signature

\- two-way SMS inbox

\- AI follow-up message suggestions

\- review monitoring

\- QuickBooks integration

\- Zapier integration

\- team scheduling calendar

\- recurring maintenance plans

\- mobile app

\- photo attachments

\- before/after job galleries

\- crew assignment

\- route planning

\- lead capture forms

\- public booking pages



\---



\# 41. Final Product Reminder



TradeLoop should prove this core value:



> A contractor finishes a job, and TradeLoop automatically turns that completed job into reviews, repeat work, and better sales tracking.



Keep version one simple, useful, and demo-ready.

