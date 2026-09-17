# Customer Q A Specification
## Purpose

Enable customers of the Smart WooCommerce FAQ plugin to submit questions and answers directly on a product page, with all submissions held for human moderation before anything is published. It spans a product-page submission form, an approve/reject moderation workflow, email notifications on new questions and new answers, dynamic product-attribute placeholder resolution in answers, and strict validation and sanitization so no unmoderated or unsanitized customer content ever reaches visitors.

## Requirements

### Requirement: Customer Q&A Submission Form on the Product Page

The plugin SHALL render a submission form on the product page that lets a customer submit a question about that product, and, when enabled, an answer to the submitted question. The form SHALL collect the customer's required identifying information and their content, SHALL only accept submissions for the product being viewed, and SHALL confirm to the customer that their submission has been received.

#### Scenario: Customer submits a question

- **WHEN** a customer on a product page completes the Q&A submission form with a question and submits it
- **THEN** the question is recorded against that product with the submitting customer's details, and the customer is shown that the submission has been received for review.

#### Scenario: Submission is bound to the correct product

- **WHEN** a customer submits a form while viewing a specific product
- **THEN** the submission is associated with exactly that viewed product and not any other.

### Requirement: Moderation Workflow for Customer Content

Every customer-submitted question and answer SHALL enter a pending state where it is invisible to visitors. A site owner SHALL approve or reject each pending submission, and only approved submissions SHALL appear publicly. Rejected submissions SHALL never be published.

#### Scenario: Question is approved by the site owner

- **WHEN** a pending customer question is approved by the site owner
- **THEN** the question, with any approved answer, becomes visible to visitors on the product page.

#### Scenario: Question is rejected by the site owner

- **WHEN** a pending customer question is rejected by the site owner
- **THEN** the question is not published and does not appear to any visitor.

#### Scenario: Pending submission not yet moderated

- **WHEN** a customer submits a question or answer that has not yet been moderated
- **THEN** that content is not visible to any visitor until it is approved.

### Requirement: Email Notifications on New Content

The plugin SHALL send an email notification to the site owner when a new customer question is submitted, and SHALL send a notification when a new customer answer is submitted. The notifications SHALL identify the affected product and the customer content so the site owner can act on the moderation queue.

#### Scenario: New question triggers a notification

- **WHEN** a customer submits a new question
- **THEN** the site owner receives an email identifying the product and the submitted question.

#### Scenario: New answer triggers a notification

- **WHEN** a customer submits a new answer
- **THEN** the site owner receives an email identifying the product and the submitted answer.

### Requirement: Product-Attribute Placeholder Resolution in Answers

The plugin SHALL support dynamic product-attribute placeholders inside customer answers, including `{product_price}` and `{stock_status}`, and SHALL resolve each placeholder to the actual value of the product at the time the answer is made public. Unsupported or unresolvable placeholders SHALL NOT display as raw text.

#### Scenario: Answer contains a supported placeholder

- **WHEN** an approved answer for a product contains the `{product_price}` and `{stock_status}` placeholders
- **THEN** the displayed answer shows that product's current price and stock status in place of the placeholders.

#### Scenario: Placeholder has no resolvable value

- **WHEN** a placeholder in an approved answer cannot be resolved to a value for the product
- **THEN** the answer is displayed without the unresolvable placeholder appearing as literal raw text.

### Requirement: Customer Content Never Published Unmoderated

Customer-submitted questions and answers SHALL NOT be published or otherwise made visible to any visitor until a site owner has approved them. No automatic publication of customer content SHALL occur under any circumstances.

#### Scenario: Submission is never auto-published

- **WHEN** a customer submits a question or answer
- **THEN** the content is held in a pending state and is never shown to visitors until a site owner explicitly approves it.

### Requirement: Validation and Sanitization of Submissions

The plugin SHALL validate every customer submission against required fields, allowed lengths, and expected content types before accepting it, and SHALL sanitize all stored content so that no raw unescaped user input is trusted or rendered. Invalid submissions SHALL be rejected rather than stored as-is.

#### Scenario: Valid submission is stored sanitized

- **WHEN** a customer submits a valid question or answer
- **THEN** the content is sanitized before storage and is later rendered safely so no raw user input is output unescaped.

#### Scenario: Invalid submission is rejected

- **WHEN** a customer submits content that fails validation, such as missing required fields or exceeding allowed length
- **THEN** the submission is rejected and is not stored without correction.

