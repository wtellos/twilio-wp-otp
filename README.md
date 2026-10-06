# Twilio WP 2FA plugin

A WordPress plugin that adds **SMS two-factor login** to the classic `wp-login.php` screen, using [Twilio Verify](https://www.twilio.com/docs/verify).

Built as a portfolio project: a native PHP MVC OTP app, rebuilt as a WordPress plugin with hooks, filters and usermeta.

## How it works

1. **Register**: the registration form has a required phone field (E.164 format, e.g. `+35799123456`). The phone is saved to usermeta (`twp_phone`).
2. **Confirm email**: the standard WordPress flow (set-password link by email).
3. **Log in**: the user enters username and password on the normal login page.
4. **Get a code**: if the password is right, the plugin stops the login, texts a code through Twilio Verify and redirects to `wp-login.php?action=twp_verify`.
5. **Verify**: the user enters the code. Only when Twilio approves it does the plugin set the login cookie.

```
wp-login.php ──password OK──▶ SMS sent ──▶ wp-login.php?action=twp_verify ──code OK──▶ dashboard
```

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- A [Twilio account](https://www.twilio.com/try-twilio) with a **Verify service**
- A Twilio **Standard API key** (a Restricted key needs the Verify permissions)

## Quick start (local)

```bash
git clone https://github.com/wtellos/twilio-wp-otp.git
cd twilio-wp-otp
docker compose up -d
```

Install the plugin's Composer dependencies (no local Composer needed):

```powershell
docker run --rm -v "${PWD}/twilio-wp-otp:/app" -w /app composer:2 install
```

Create your `.env` from the template and replace every value with your own Twilio keys:

```bash
cp twilio-wp-otp/.env.example twilio-wp-otp/.env
```

| Variable | Where to find it |
|---|---|
| `TWILIO_ACCOUNT_SID` | Twilio Console dashboard (starts with `AC`) |
| `TWILIO_API_KEY` | Console → Account → API keys & tokens (starts with `SK`) |
| `TWILIO_API_SECRET` | Shown once when you create the API key |
| `TWILIO_VERIFY_SID` | Console → Verify → Services (starts with `VA`) |

Then open <http://localhost:8080>, finish the WordPress install and:

1. **Plugins** → activate **Twilio WP 2FA OTP**
2. **Settings → General** → tick **Anyone can register**

## Trial account note

On a Twilio trial account, SMS only goes to phone numbers you have verified in the Twilio Console.

## Project structure

```
twilio-wp-otp/
├── twilio-wp-otp.php            # plugin header, constants, autoload, bootstraps the controllers
├── includes/
│   ├── RegistrationController.php   # phone field: render, validate, save to usermeta
│   └── AuthController.php           # Twilio Verify client, login intercept, verify screen
├── assets/css/twp.css
├── assets/js/twp.js
├── composer.json                # twilio/sdk, vlucas/phpdotenv, PSR-4 TwilioWpOtp\ → includes/
└── .htaccess                    # blocks direct access to .env
```

## Security notes

- Secrets live in `twilio-wp-otp/.env`, which is git-ignored and blocked by `.htaccess` (Apache). Never commit it.
- The pending login is stored as a single-use token (transient, 10 minutes). It is deleted as soon as the code is approved.
- The login cookie is only set **after** Twilio approves the code.

## Known limitations

- Users without a saved phone (for example, accounts created in the WP dashboard) log in without 2FA.
- Twilio errors (expired code, too many attempts) are not handled yet and can show a fatal error.
- No rate limiting or nonce on the verify form yet.

## Roadmap

- [x] Registration phone field
- [x] Login intercept, SMS code, verify screen
- [x] Verify code and set the login cookie
- [ ] Hardening: nonce, rate limits, friendly Twilio errors (60202 / 60203), Lookup, Fraud Guard
- [ ] Redirect logged-in users away from the login and verify screens
- [ ] GitHub Actions PHP lint