**Name:** Sliced Invoices API Connector
**Version:** 1.0.0
**Description:** Extends the features of Sliced Invoice plugin to be reachable by external apps via WP native REST API.
**License:** GNU GPLv3

#### Details:
*   Quotes CRUD
*   Invoices CRUD

#### Endpoints:
*   Quotes
    *   /wc-connector/v1/quotes
        *   **Method:** GET
        *   **Description:** List all quotes
    *   /wc-connector/v1/quotes
        *   **Method:** POST
        *   **Description:** Store a new quote
        *   **Params:**
            *   customer_email
                *   Required
                *   Valid email address
            *   items
                *   Required
                *   Array of items, each item with a valid **title**, **qty** and **amount**
            *   action
                *   Required.
                *   Valid options are **Draft** and **Sent**
            *   discount
                *   Optional
                *   Integer
    *   /wc-connector/v1/quotes/{quote_id}
        *   **Method:** PUT | PATCH
        *   **Description:** Update an existing quote
        *   **Params:**
            *   customer_email
                *   Required
                *   Valid email address
            *   items
                *   Required
                *   Array of items, each item with a valid **title**, **qty** and **amount**
            *   action
                *   Required.
                *   Valid options are **Draft** and **Sent**
            *   discount
                *   Optional
                *   Integer
    *   /wc-connector/v1/quotes/{quote_id}
        *   **Method:** DELETE
        *   **Description:** Delete an existing quote
*   Invoices
    *   /wc-connector/v1/invoices
        *   **Method:** GET
        *   **Description:** List all invoices
    *   /wc-connector/v1/invoices
        *   **Method:** POST
        *   **Description:** Store a new invoice
        *   **Params:**
            *   customer_email
                *   Required
                *   Valid email address
            *   items
                *   Required
                *   Array of items, each item with a valid **title**, **qty** and **amount**
            *   action
                *   Required.
                *   Valid options are **Draft** and **Sent**
            *   discount
                *   Optional
                *   Integer
    *   /wc-connector/v1/invoices/{invoice_id}
        *   **Method:** PUT | PATCH
        *   **Description:** Update an existing invoice
        *   **Params:**
            *   customer_email
                *   Required
                *   Valid email address
            *   items
                *   Required
                *   Array of items, each item with a valid **title**, **qty** and **amount**
            *   action
                *   Required.
                *   Valid options are **Draft** and **Sent**
            *   discount
                *   Optional
                *   Integer
    *   /wc-connector/v1/invoices/{invoice_id}
        *   **Method:** DELETE
        *   **Description:** Delete an existing invoice

#### Installation:

1.  Upload the entire sliced-invoices-api-connector folder to the /wp-content/plugins/ directory.
2.  Activate the plugin through the ‘Plugins’ menu in WordPress.

#### Notes:

1.  Both, WooCommerce and Sliced Invoices must be activated.
1.  The plugin uses [WC Authentication methods](https://woocommerce.github.io/woocommerce-rest-api-docs/#authentication).
1.  Site with SSL is recommended.
1.  Internally the plugin will resolve the **customer_email** parameter to an existing user, if no user exists with that email, it will be created.
