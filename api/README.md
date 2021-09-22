# Public API

This is built as a Google service under the main project and handles requests for URLs like `http://<SERVER>/api/*`

This service only handles non-ssl requests because it is designed to be the end point for low power devices like ESP8266's which don't handle SSL certificate management all that well.

When running this on your local dev machine, run it on port 8085. This is expected within the software when it detects being run on localhost.
