## Purpose

Provide privacy-respecting, opt-in AI assistance for creating and enriching FAQ content across the Smart WooCommerce FAQ plugin. It spans multi-provider generation, adjustable copywriting, objection-buster templates, resilience, bulk operation, review mining, chatbot retrieval, and clear marking of machine-authored content, while ensuring every AI call stays server-side with API keys never exposed to browsers, logs, or responses.

## ADDED Requirements

### Requirement: Bring-Your-Own-Key Multi-Provider Generation

The plugin SHALL let the site owner generate FAQ answers using an AI provider chosen from OpenAI, Google Gemini, or Claude, each with its own server-side, user-supplied API key. Each provider SHALL be independently configured and enabled before any outbound request is attempted. The selected provider and its settings SHALL be the sole configuration required to activate generation.

#### Scenario: Generate an FAQ answer for a single question

- **WHEN** the plugin has an API key configured for an enabled provider and a user triggers generation for a single FAQ question
- **THEN** the plugin requests an answer from that provider over a server-side HTTPS connection and presents the returned answer as a draft for the question.

#### Scenario: No provider configured

- **WHEN** no API key is configured for any provider and a user triggers generation
- **THEN** the feature remains fully dormant, no outbound request is made, and the user is shown that generation is unavailable until a key is configured.

### Requirement: Adjustable Copywriting Tone

The plugin SHALL support four selectable copywriting tones for generated answers: Sales/Objection-Buster, Friendly, Concise, and Technical. The active tone SHALL be applied to every generated answer until the site owner changes it, and the tone choice SHALL affect only the wording and style, never the factual content requested.

#### Scenario: Generate an answer in a selected tone

- **WHEN** the site owner selects the Friendly tone and triggers generation for a question
- **THEN** the returned draft is phrased in a warm, approachable tone while still addressing the question.

#### Scenario: Change tone for subsequent generations

- **WHEN** the site owner changes the tone from Sales/Objection-Buster to Technical
- **THEN** subsequently generated answers follow the newly selected tone.

### Requirement: One-Click Objection-Buster Templates

The plugin SHALL ship predefined objection-buster prompt templates for Shipping & Delivery, Returns & Money-Back, Warranty & Durability, Sizing & Compatibility, and Care & Maintenance. A site owner SHALL apply a template with a single action to generate objection-handling FAQ answers tailored to that topic.

#### Scenario: Apply a template to a question

- **WHEN** the site owner selects the Returns & Money-Back template for a question
- **THEN** the plugin generates a draft that directly addresses the return and money-back objection raised.

### Requirement: Multi-Model Automatic Failover

When generation is requested for an already-configured provider that is unavailable, the plugin SHALL automatically attempt the next configured provider in a deterministic order, continuing until one succeeds or all are exhausted, without any manual intervention by the site owner.

#### Scenario: Primary provider is unavailable

- **WHEN** the active provider returns an error and a second provider is configured
- **THEN** the plugin retries the request with the second provider and returns a successful draft if that provider responds.

#### Scenario: All providers are unavailable

- **WHEN** every configured provider returns an error for a generation request
- **THEN** the plugin reports that generation failed and makes no partial or fabricated answer available.

### Requirement: Bulk Category Generation as a Background Job

The site owner SHALL be able to trigger AI FAQ generation across an entire product category. The task SHALL run as a background job so that large catalogs complete without hitting the PHP request timeout, and the site owner SHALL be able to observe progress and result of the job.

#### Scenario: Generate FAQs for a large category

- **WHEN** the site owner triggers bulk generation for a product category containing many products
- **THEN** the work runs as a background job that continues beyond a single HTTP request, and each product's generated FAQs become available as drafts once processed.

#### Scenario: Bulk generation with no configured provider

- **WHEN** the site owner triggers bulk generation while no API key is configured
- **THEN** the job records that generation is unavailable and performs no outbound calls.

### Requirement: AI Auto-Draft of Customer Question Answers

The plugin SHALL be able to generate answer drafts for customer-submitted questions. Such answers SHALL be stored as drafts and MUST NOT be auto-published or otherwise made visible to other visitors until a human accepts them.

#### Scenario: Drafting an answer to a submitted question

- **WHEN** the plugin generates an answer draft for a customer-submitted question
- **THEN** the answer is saved in draft state, is not published automatically, and requires a human to review and publish it before it becomes public.

### Requirement: Review-Mined FAQ Suggestions

The plugin SHALL be able to mine existing product reviews and return AI-suggested FAQ entries derived from recurring questions or concerns found within them. A site owner SHALL review such suggestions before they become actual FAQ entries.

#### Scenario: Suggest FAQs from reviews

- **WHEN** the site owner requests FAQ suggestions from a product's reviews
- **THEN** the plugin returns suggested FAQ entries derived from the review content, held for the site owner to accept or reject.

### Requirement: Retrieval-Based FAQ Chatbot Widget

The plugin SHALL provide a chatbot widget on the product page that answers questions by retrieving from the product's FAQ content. It SHALL use the same server-side AI key configured for generation, and every answer request SHALL travel through the server so that the AI provider key is never exposed to the browser.

#### Scenario: Visitor asks a question in the widget

- **WHEN** a visitor submits a question in the product page chatbot widget
- **THEN** the request is handled server-side, an answer is retrieved and returned to the widget, and no AI key is exposed to the visitor's browser.

#### Scenario: Chatbot with no configured provider

- **WHEN** a visitor uses the widget while no AI key is configured
- **THEN** the widget offers no AI answers and performs no outbound calls.

### Requirement: Server-Side AI Calls and Key Protection

All AI requests SHALL be made from the server. API keys SHALL be stored server-side, MUST NOT be shipped to or rendered in the browser, MUST NOT be written to logs, and MUST NOT appear in any API response. Keys SHALL be treated as secrets under server-side protection.

#### Scenario: Key never exposed during a generation request

- **WHEN** the plugin makes an AI call for generation
- **THEN** the request is issued from the server, the key is not transmitted to the browser, and the key does not appear in logs or in any API response body.

#### Scenario: Key remains hidden under all conditions

- **WHEN** an AI request fails or the plugin reports an error
- **THEN** error output never includes the API key.

### Requirement: AI-Generated Content Marking

AI-generated FAQ answers SHALL be visibly marked as machine-generated until a human edits or approves them. The marking SHALL be removed only upon human editing or explicit approval.

#### Scenario: Generating a new AI answer

- **WHEN** the plugin generates an answer
- **THEN** the answer is clearly marked as AI-generated and remains so until a human edits or approves it.

#### Scenario: Human approval clears the marking

- **WHEN** a human edits or approves an AI-generated answer
- **THEN** the machine-generated marking is removed from that answer.

### Requirement: Strictly Opt-In Generation

Generation SHALL require an explicitly configured API key. Without any configured API key, the AI feature SHALL be fully dormant and SHALL make no outbound network calls for any AI capability, including generation, bulk, review mining, auto-draft, and chatbot.

#### Scenario: Feature dormant without a key

- **WHEN** no API key is configured
- **THEN** no AI feature attempts any outbound call and no user-facing AI functionality is active.

#### Scenario: Activation upon key configuration

- **WHEN** the site owner configures at least one API key
- **THEN** the AI capabilities become available according to the configured provider settings.