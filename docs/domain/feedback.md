# Feedback form

A fixed contact form with three fields: **name**, **email**, **message**. Submissions are
emailed to the form's configured recipient and are not stored.

## Backend

- **`feedbackElement`** (`trickster-cms/homepage/modules/structureElements/feedback/`) — a plain
  `content` page element. Admin-editable fields: `title` (email subject + page heading),
  `destination` (recipient email), `content` (intro HTML above the form). Its public view renders
  the Angular `<zx-feedback-form element-id="{id}">` custom element.
- **`ZxArt\Controllers\Feedback`** (`/feedback/`) — REST endpoint. Accepts `POST /feedback/?id={elementId}`
  with a JSON body `{name, email, message}`. Validates required fields and email format, then delegates
  to `FeedbackService`. Returns `{success: true}` on success, `{errorMessage}` with an HTTP error code
  otherwise (422 = email rejected by anti-spam).
- **`ZxArt\Feedback\FeedbackService`** — validates the sender email via `EmailValidationService`, then
  sends the message with `EmailDispatcher` using dispatchment type `feedbackForm`. The sender address is
  set as **Reply-To**; `From` is the site's `default_sender_email` for deliverability. The recipient is
  the element's `destination` (falls back to `default_sender_email`).
- **Email template**: dispatchment type `feedbackForm`
  (`trickster-cms/homepage/modules/dispatchmentTypes/feedbackForm.class.php`) with content template
  `templates/document/content.feedbackForm.tpl`.

## Email validation (shared with registration)

`ZxArt\Email\EmailValidationService` + `ZxArt\Email\DomainBanRepository` decide whether an address is
acceptable. `isAllowed()` applies: local-part heuristics (`+`, more than 2 dots) → cached domain ban list
(`domains` table) → external services (`VerifaliaAdapter`, `VerifyMailAdapter`). A domain is blocked only
when an external service explicitly rejects it; the decision is cached in `domains`. The
`submitRegistration` action uses the same service.

## Reply-To

`EmailDispatchment` carries an optional `replyTo` (stored in `email_dispatchments.replyTo`, passed to
PHPMailer `AddReplyTo`).

## Frontend

`features/feedback/` — `ZxFeedbackFormComponent` (registered custom element `zx-feedback-form`) built
from design-system primitives (`zx-form`, `zx-input`, `zx-textarea`, `zx-button`). Submits via
`FeedbackApiService` to `/feedback/`. Translations live under the `feedback.*` i18n keys (en/ru/es).
