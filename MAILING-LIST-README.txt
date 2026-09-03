POG African Safaris — Mailing List
==================================

WHAT WAS ADDED
- subscribe.php ........ receives the footer newsletter signup and the contact
                         form, and appends each entry to the CSV.
- data/mailing-list.csv  the mailing list (created automatically on first signup).
                         Columns: Date, Name, Email, Phone, Package, Message, Source
- data/.htaccess ....... blocks anyone from downloading the CSV directly.
- mailing-list.php ..... password-protected page to view and download the list.
- config.php ........... where you set the password.

SETUP (2 minutes)
1. Upload the whole site to hosting that supports PHP (almost all shared
   hosting does: cPanel, Afrihost, Xneelo, Hostinger, GoDaddy, etc.).
2. Open config.php and change:
       define('MAILING_LIST_PASSWORD', 'ChangeMe-POG-2026');
   to your own password.
3. Make sure the data/ folder is writable (permissions 755 or 775).

USING IT
- Visit  https://yourdomain.com/mailing-list.php
- Enter the password, view all signups, and click "Download CSV".
- The page is marked noindex, so search engines will not list it.

NOTES
- The CSV lives in data/ and is blocked from direct web access by .htaccess
  (Apache). If your host runs nginx, ask them to deny access to /data/.
- Both forms show a confirmation to the visitor and reset after sending.

CONTACT FORM EMAIL
------------------
Contact enquiries are emailed via PHP mail() to the address in config.php:
  ENQUIRY_TO   = info@pog.com          <- change to the real inbox
  ENQUIRY_FROM = noreply@pogafricansafaris.com  <- must be on your own domain
Reply-To is set to the enquirer, so hitting Reply answers them directly.
Every enquiry is also still saved to the mailing list CSV.

SPAM PROTECTION
---------------
1. Honeypot: a hidden "website" field is added to both forms; if it is filled,
   the submission is rejected.
2. Time check: submissions made less than 3 seconds after the page loads are
   rejected.
