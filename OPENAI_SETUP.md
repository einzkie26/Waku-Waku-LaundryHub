# Ask Waku: free FAQ mode

Ask Waku now uses the built-in Laundryhub guide by default. It answers booking, process, pricing, products, extras, tracking and garment-care questions from the shop's FAQ content. These are prepared guide answers with follow-up suggestions, rather than generated AI replies.

No API key, credit purchase or external AI service is needed. The same free website assistant works in Windows and Samsung browsers.

```dotenv
LAUNDRY_ASSISTANT_OPENAI_ENABLED=false
```

This switch disables OpenAI calls even if a key is still saved. The connection checker also stays local in this mode:

```powershell
php artisan config:clear
php artisan laundry:assistant-check
```

Expected result: **Free FAQ mode is active. No OpenAI requests are made.** Refresh the main page and open **Ask Waku**. Replies show **Laundryhub guide**.

## Optional paid OpenAI integration

Keep the setting above `false` for free operation. The following setup applies only if the owner later explicitly chooses paid generated answers.

1. Sign in at [OpenAI API keys](https://platform.openai.com/api-keys), select your project, and create a new secret key. Keep the value private.
2. Check [API billing](https://platform.openai.com/settings/organization/billing/overview) and available credits for the project you use.
3. Open the project's private `.env` file. The settings have already been added locally. Paste your real key after `OPENAI_API_KEY=` and save:

```dotenv
LAUNDRY_ASSISTANT_OPENAI_ENABLED=true
OPENAI_API_KEY=your-real-key-here
OPENAI_MODEL=gpt-4.1-mini
LAUNDRY_ASSISTANT_DAILY_AI_LIMIT=200
```

4. In PowerShell, from the project directory, run:

```powershell
Set-Location D:\projects\laravel\Waku-Waku-LaundryHub
php artisan config:clear
php artisan laundry:assistant-check
```

Only when paid AI is enabled, the check makes one small OpenAI request with no customer data. It never displays the key or provider response body. It reports missing/invalid keys, permission/model access problems, quota/rate limits, and connection failures.

If the result mentions `insufficient_quota`, exhausted credits, or a spending limit, review the API organization's Billing page and the project limits. Add credits or adjust the applicable limit before checking again; repeated requests do not resolve billing errors. A temporary rate limit is a different issue and may require waiting. See [OpenAI's error-code guidance](https://developers.openai.com/api/docs/guides/error-codes).

5. After the connection succeeds, refresh the main page, open **Ask Waku**, and ask a laundry question. A generated reply says **AI answer**. **Laundryhub guide** means a built-in FAQ reply; the site falls back to that if AI is unavailable or its daily cap is reached.

Restart a long-running PHP application process if it still uses old settings. Keep the key in the server's `.env`, never public JavaScript or a `VITE_*` variable. `.env` is excluded from Git.

Official references: [OpenAI quickstart](https://developers.openai.com/api/docs/quickstart), [API keys and service accounts](https://developers.openai.com/api/docs/guides/api-keys).
