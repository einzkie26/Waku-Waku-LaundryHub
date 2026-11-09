# Laundryhub for Bixby

This is the separate Samsung Bixby capsule source, not an embedded website chatbot. Bixby selects one of 20 FAQ topics, calls the Laravel guide endpoint, and speaks and displays the answer. Prices and units therefore come from the same PHP catalog as the booking form. It does not access customer accounts, look up orders, or consume the OpenAI API budget.

The source includes concept/action models, vocabulary, a result view, the authenticated HTTP adapter, and 40 Aligned NL examples for Studio's Bulk Add tool. It has not been compiled in Bixby Developer Studio or submitted to Samsung from this workspace. Actual device/locale availability and approval must be verified in your Samsung developer account.

## Connect and test

1. Deploy the Laravel app to a public HTTPS address and set its `APP_URL` correctly. Bixby's servers cannot reach `localhost`.
2. Generate a private token with `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`. Store it as `LAUNDRY_BIXBY_TOKEN` in the server's `.env`. Do not commit it or put it in website JavaScript. Run `php artisan config:clear` (or rebuild the deployment's config cache).
3. Open this folder as a capsule in Bixby Developer Studio. Replace `example.laundryhub` in `capsule.bxb` with your registered namespace. The example namespace cannot be submitted for device testing.
4. Set `remote.url` in `resources/base/capsule.properties` to the public origin. In Bixby Developer Center, Configuration & Secrets, set the secret `laundryhub.apiToken` to the same private token. The capsule reads it with `secret.get()`.
5. In Studio, select the `en` training source and **Bulk Add**. Paste the lines in `training-aligned-nl.txt`. Studio creates the native training files; do not hand-edit its generated training files. Compile the NL model and resolve all issues before submission.
6. Test all 20 topics in the simulator, including these intents: `intent { goal: LaundryAnswer value: LaundryTopic (Booking) }` and `intent { goal: LaundryAnswer value: LaundryTopic (Softener) }`. Test the equivalent natural-language questions, failure handling, and the actual Galaxy device using a private submission.
7. Configure the display/invocation name and supported countries, complete Samsung's submission requirements, and publish through your account. Only after successful device testing, set `LAUNDRY_BIXBY_PUBLISHED=true` and `LAUNDRY_BIXBY_NAME` to the actual approved name on the Laravel server. This enables a small Bixby hint for recognizable Samsung browser/device user agents. User-agent detection is only a hint and does not switch the website's AI provider.

The private endpoint is `POST /api/laundry-assistant/bixby` with `Authorization: Bearer <token>` and JSON such as `{"topic":"softener"}`. It returns `title`, `answer`, and `website`. Missing configuration returns 503; an invalid/missing token returns 401; unsupported topics return 422. It does not use browser cookies or CSRF exemptions on website routes.

## Official references

- [Bixby platform overview](https://bixbydevelopers.com/dev/docs/get-started/overview)
- [Action implementation and secrets](https://bixbydevelopers.com/dev/docs/dev-guide/developers/modeling.actions.js-actions)
- [HTTP API and JSON POST](https://bixbydevelopers.com/dev/docs/reference/apis/js_api.http)
- [Natural-language training and Bulk Add](https://bixbydevelopers.com/dev/docs/dev-guide/developers/handling-nl.training-for-nl)
- [Vocabulary for enums](https://bixbydevelopers.com/dev/docs/dev-guide/developers/handling-nl.vocabulary)
