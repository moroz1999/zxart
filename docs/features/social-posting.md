# Social posting — implementation

Domain rules: [../domain/social-posting.md](../domain/social-posting.md).

Newly created pictures, tunes, prods and releases are announced in the Telegram channel.

## Flow

- `persistElementData()` of a newly created element puts its id into the `social_post` queue (`QueueType::SOCIAL_POST`).
- `Socialpost` controller (`/socialpost/`, run by cron) calls `SocialPostsService::processQueue()`, which walks the queue for up to 300 seconds.
- `SocialPostFilter` skips prods without an image, and releases where neither the release nor its prod has an image. A release is also skipped when its prod is posted in the same run or is still waiting in the queue.
- `SocialPostTransformer` picks a per-type transformer and builds a `PostDto`; `PostService` sends it as `sendAudio` (when the DTO has audio), `sendPhoto` (when it has an image) or `sendMessage`.

## Post content

`PostService::formatText()` renders `<b>title</b>`, then the description, then the link.

Title is built per type:

- picture, tune — `PostTitleBuilder`: element title, plus ` / ` and the comma-separated author names when the element has authors.
- prod, release — plain element title.

Description sources:

| type | source |
|---|---|
| picture | `zxPictureElement::getTextContent()` — the `descriptions.picture` translation template |
| tune | `zxMusicElement::getTextContent()` — the `descriptions.music` translation template |
| prod | `zxProdElement::getMetaDescription()` — generated SEO meta from `module_zxprod_meta` |
| release | `zxReleaseElement::getTextContent()` — generated facts string |

The `descriptions.*` templates carry `%t %a %p %g %y` placeholders (title, authors, party, release, year). Removing a placeholder from the translation silently drops that data from the post.
