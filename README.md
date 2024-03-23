# swissqr
Script for generating Swiss QR-Invoices over Kimai
(https://www.kimai.org)

# IMPORTANT DISCLAIMER
USE AT YOUR OWN RISK
THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND.

# Generating Swiss QR-Code-Invoice from Kimai

How to Use:
1. Install qrencode on your system, it must be placed under "/usr/bin/qrencode" *
2. Copy the folder "swissqr" under the public folder of Kimai ("/opt/kimai/public")
3. Make sure the user of your Kimai-Installation has access to the folder "swissqr"
4. Adjust the template "ch_qrcode_invoice.pdf.twig" from the folder "_templates" to your needs 
5. Copy the adjusted template to an location where your Kimai-Installation can find it, e.g. "/opt/kimai/var/templates"
6. Make sure the user of your Kimai-Installation has access to the template
7. Create your invoice with the adjusted template

*  If you are running Docker, you can build an adjusted image with qrencode, included by creating an custom Dockerfile and building this image.

Remarks:
- It uses the Kimai field {{ invoice['template.payment_details'] }} as IBAN, thus there should be no other info in this field. Just one line with your IBAN (spaces are ok, they get trimmed).
- I did not figure out how to extract the 2-character country-code for the owner, thats why I used a hard coded countrycode in the twig-template, but its only useful for ch-people anyway ("ch_qrencode_company_country")
- The field additinal-information is taken from the project description if set ({{ invoice['query.project.comment'] }}) or from the project name ({{ invoice['project.name'] }})
- The php-script ist accessed via the loopback-interface ("http://127.0.0.1:8001/swissqr"), this can be changed in the template if the script is run on another server. (I did not use the asset-function, because of some quoting issue)
- There are some sanity-checks in the php-script done, but still make sure the provided info is correct and valid.
- There might be security issues with code, so please make sure you understand the code and you know what you are doing.
- The PHP-GD library is used for displaying errors (textfeedback as image)

How it works:
- the information from the invoice is send as an image-url to an php-script, which generates the qr-code and sends the output back as an image.

Used References/Code:
- https://www.six-group.com/dam/download/banking-services/standardization/qr-bill/ig-qr-bill-v2.2-de.pdf
- https://stackoverflow.com/questions/20983339/validate-iban-php
- https://gist.githubusercontent.com/benedict-w/5644085/raw/bc5536bbe9df4127de3d22a523825a137e87363b/iso_4217_currency_codes.php
- https://fonts.google.com


