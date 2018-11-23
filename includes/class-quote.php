<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.topfloormarketing.net
 * @since      1.0.0
 *
 * @package    Sliced_Invoices_Api_Connector
 * @subpackage Sliced_Invoices_Api_Connector/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Quote
 * @subpackage Sliced_Invoices_Api_Connector/includes
 * @author     JC Perez <jn.perez@topfloormarketing.net>
 */
class Quote extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc-connector/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'quotes';

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			// List Quotes
			array(
				'methods'				=> WP_REST_Server::READABLE,
				'callback'				=> array( $this, 'get_items' ),
				'permission_callback' 	=> array( $this, 'get_items_permissions_check' ),
				'args'					=> $this->get_collection_params(),
			),

			// Create Quote
			array(
				'methods'				=> WP_REST_Server::CREATABLE,
				'callback'				=> array( $this, 'create_item' ),
				'permission_callback'	=> array( $this, 'create_item_permissions_check' ),
				'args'					=> $this->create_item_args(),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			// View Quote
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(
					'context' => array(
						'default' => 'view',
					),
				),
			),

			// Update Quote
			array(
				'methods'               => WP_REST_Server::EDITABLE,
				'callback'              => array( $this, 'update_item' ),
				'permission_callback'   => array( $this, 'update_item_permissions_check' ),
				'args'                  => $this->update_item_args(),
			),

			// Delete Quote
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'                => array(
					'force' => array(
						'default' => false,
					),
				),
			),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/schema', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Get a collection of quotes
	 *
	 * @param 	WP_REST_Request $request Full data about the request.
	 * @return 	WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$parameters = $request->get_query_params();

	    $args = array(
	    	'post_type'			=> 'sliced_quote',
	        'posts_per_page'	=> 5,
	    );

	    $collection = array();
	    $quotes 	= get_posts($args);

	    if ( empty( $quotes ) ) {
	        return rest_ensure_response( $collection );
	    }

	    foreach ( $quotes as $quote ) {
	    	$data 			= $this->prepare_quote_for_response( $quote, $request );
	    	$collection[] 	= $this->prepare_response_for_collection( $data );
	    }

    	return rest_ensure_response( $collection );
	}

	/**
	 * Get one quote from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$params = $request->get_params();
        $q      = get_post( $params['id'] );

		if ( ! empty( $q ) && 'sliced_quote' == get_post_type( $params['id'] ) ) {
			return $this->prepare_quote_for_response( $q, $request );
		}

		return new WP_Error( 'rest_quote_invalid_id', __( 'Invalid quote ID.', 'sliced-invoices-api-connector' ), array( 'status' => 404 ) );
	}

	/**
     * Get the argument schema for this example endpoint.
     */
    public function create_item_args() {
        $args = array();

        $args['customer_email'] = array(
            'description'  => esc_html__( 'The customer\'s email address', 'sliced-invoices-api-connector' ),
            'type'         => 'string',
            'required'     => true,
            'format'       => 'email'
        );

        $args['items'] = array(
            'description'  => esc_html__( 'An array containing the items for the quote. Name, Quantity and Price.', 'sliced-invoices-api-connector' ),
            'type'         => 'array',
            'required'     => true
        );

        $args['action'] = array(
            'description'   => esc_html__( 'Action to execute with the quote. Valid values are Draft | Sent.', 'sliced-invoices-api-connector' ),
            'type'          => 'string',
            'required'      => true,
            'enum'          => array( 'Draft', 'Sent' ),
        );

        $args['discount'] = array(
            'description'  => esc_html__( 'The discount applied to the quote. Must be integer.', 'sliced-invoices-api-connector' ),
            'type'         => 'integer',
        );
     
        return $args;
    }

    /**
	 * Get the argument schema for this example endpoint.
	 */
	public function update_item_args() {
	    $args = array();

	    $args['id'] = array(
	        'description'  => esc_html__( 'The Quote ID', 'sliced-invoices-api-connector' ),
	        'type'         => 'integer',
	        'required'     => true,
	    );
	 
	    return array_merge($args, $this->create_item_args());
	}

    public function validate_create_item_request( $request ) {

        // Customer email
        if ( ! is_email( $request['customer_email'] ) ) {
            return new WP_Error(
                'rest_quote_invalid_param',
                __( 'Customer email parameter must be a valid email address.', 'sliced-invoices-api-connector' ),
                array(
                    'status' => 400
                )
            );
        }

        // Items
        $keys = ['title', 'qty', 'amount'];

        foreach ($request['items'] as $item) {
            foreach ($keys as $k) {
                if ( ! array_key_exists( $k, $item ) ) {
                    return new WP_Error(
                        'rest_quote_missing_param_key',
                        __( 'Missing key "' . $k . '" from item collection.', 'sliced-invoices-api-connector' ),
                        array(
                            'status' => 400
                        )
                    );
                }

                if ( empty( $item[ $k ] ) ) {
                    return new WP_Error(
                        'rest_quote_empty_param_key',
                        __( 'Key "' . $k . '" from item collection cannot be empty.', 'sliced-invoices-api-connector' ),
                        array(
                            'status' => 400
                        )
                    );
                }
            }
        }

        return $request;
    } 

	/**
	 * Create one quote from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function create_item( $request ) {
        $params = $this->validate_create_item_request( $request );

        if ( is_wp_error( $params ) ) {
            return $params;
        }

		$id = wp_insert_post( $this->prepare_item( $params ), true );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

        $term = term_exists($params->get_param('action'), 'quote_status');

        if ( empty( $term ) ) {
            return new WP_Error(
                'rest_quote_invalid_term',
                __( 'The selected status doesnt exists for quotes.', 'sliced-invoices-api-connector' ),
                array(
                    'status' => 400
                )
            );
        }

        wp_set_post_terms( $id, [$term['term_id']], 'quote_status' );

        if ( $params->get_param('action') == 'Sent' ) {
            $n = new Sliced_Notifications();
            $n->send_the_quote( $id );
        }
	 
		return new WP_REST_Response( $this->prepare_quote_for_response( get_post( $id ), $request ) , 201 );
	}

	/**
	 * Update one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function update_item( $request ) {
        $params = $this->validate_create_item_request( $request );

        if ( is_wp_error( $params ) ) {
            return $params;
        }

        $term = term_exists($params->get_param('action'), 'quote_status');

        if ( empty( $term ) ) {
            return new WP_Error(
                'rest_quote_invalid_term',
                __( 'The selected status doesnt exists for quotes.', 'sliced-invoices-api-connector' ),
                array(
                    'status' => 400
                )
            );
        }

        update_post_meta( $params->get_param('id'), '_sliced_client', $this->getOrCreateCustomer( $params->get_param('customer_email') ) );
        update_post_meta( $params->get_param('id'), '_sliced_items', $params->get_param('items') );
        update_post_meta( $params->get_param('id'), 'sliced_invoice_discount', $params->get_param('discount') );
        wp_set_post_terms( $params->get_param('id'), [$term['term_id']], 'quote_status' );

		if ( $params->get_param('action') == 'Sent' ) {
            $n = new Sliced_Notifications();
            $n->send_the_quote( $params->get_param('id') );
        }

        return new WP_REST_Response( $this->prepare_quote_for_response( get_post( $params->get_param('id') ), $request ) , 202 );
	}

	/**
	 * Delete one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function delete_item( $request ) {
        wp_delete_post( $request->get_param('id'), $request->get_param('force') );

		return new WP_REST_Response( [], 204 );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_rest_check_user_permissions( 'read' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
 
	/**
	 * Check if a given request has access to get a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
    	$id = (int) $request['id'];

		if ( ! wc_rest_check_user_permissions( 'read', $id ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot view this resource.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
 
	/**
	 * Check if a given request has access to create items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
    	if ( ! wc_rest_check_user_permissions( 'create' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_create', __( 'Sorry, you are not allowed to create resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
 
	/**
	 * Check if a given request has access to update a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
    	$id = (int) $request['id'];

		if ( ! wc_rest_check_user_permissions( 'edit', $id ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_edit', __( 'Sorry, you are not allowed to edit this resource.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
 
	/**
	 * Check if a given request has access to delete a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		$id = (int) $request['id'];

		if ( ! wc_rest_check_user_permissions( 'delete', $id ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_delete', __( 'Sorry, you are not allowed to delete this resource.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
 
	/**
	 * Prepare the item for create or update operation
	 *
	 * @param  WP_REST_Request $request Request object
	 * @return WP_Error|object $prepared_item
	 */
	private function prepare_item( $request ) {
        $s = new Sliced_Quote();
        $s->get_next_quote_number();

        return [
            'post_title'        => "QUO-{$s->get_next_quote_number()}",
            'post_type'         => 'sliced_quote',
            'post_status'       => 'publish',
            'comment_status'    => 'closed',
            'ping_status'       => 'closed',
            'meta_input'        => [
                '_sliced_quote_prefix'      => "QUO-",
                '_sliced_quote_number'      => $s->get_next_quote_number(),
                '_sliced_client'            => $this->getOrCreateCustomer( $request['customer_email'] ),
                '_sliced_items'             => $request['items'],
                '_sliced_quote_created'     => current_time( 'timestamp' ),
                'sliced_invoice_discount'   => $request['discount']
            ]
        ];
	}
 
	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_quote_for_response( $item, $request ) {
		$m = get_post_meta( $item->ID );
		$c = get_userdata( $m['_sliced_client'][0] );
        $s = get_the_terms( $item->ID, 'quote_status' );

	    return rest_ensure_response([
	    	'id' 			=> $item->ID,
	    	'number'		=> $m['_sliced_quote_prefix'][0] . $m['_sliced_quote_number'][0],
	    	'status' 		=> $s[0]->name,
	    	'customer'		=> [
	    		'name'	=> $c->nickname,
	    		'email'	=> $c->user_email
	    	],
	    	'items'			=> unserialize( $m['_sliced_items'][0] ),
            'discount'      => $m['sliced_invoice_discount'][0],
	    	'created_at'	=> $m['_sliced_quote_created'][0]
	    ]);
	}
 
	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'page'     => array(
				'description'       => 'Current page of the collection.',
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
			),

			'per_page' => array(
				'description'       => 'Maximum number of items to be returned in result set.',
				'type'              => 'integer',
				'default'           => 10,
				'sanitize_callback' => 'absint',
			),
			'search'   => array(
				'description'       => 'Limit results to those matching a string.',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	public function get_item_schema() {
		return [
	        '$schema'		=> 'http://json-schema.org/draft-04/schema#',
	        'title'			=> 'quote',
	        'type'			=> 'object',
	        'properties'	=> array(
	            'id'		=> [
	            	'description'  => esc_html__( 'Unique identifier for the object.', 'sliced-invoices-api-connector' ),
	            	'type'         => 'integer',
	            	'context'      => array( 'view', 'edit', 'embed' ),
	            	'readonly'     => true,
	            ],
	            'customer'	=> array(
	                'description'  => esc_html__( 'The the customer object.', 'sliced-invoices-api-connector' ),
	                'type'         => 'array',
	            ),
	            'number'	=> array(
	                'description'  => esc_html__( 'The number of the quote.', 'sliced-invoices-api-connector' ),
	                'type'         => 'string',
	            ),
	            'status'	=> array(
	                'description'  => esc_html__( 'The status of the quote.', 'sliced-invoices-api-connector' ),
	                'type'         => 'string',
	            ),
		    	'items'	=> array(
	                'description'  => esc_html__( 'A list of included items.', 'sliced-invoices-api-connector' ),
	                'type'         => 'array',
	            ),
		    	'created_at'	=> array(
	                'description'  => esc_html__( 'The date when the quote was created.', 'sliced-invoices-api-connector' ),
	                'type'         => 'string',
	            )
	        )
	    ];
	}

    /**
     * Get a user or create a user in case this doesnt exists.
     * @param   str $email  Valid email adress
     * @return  int $id     Customer ID
     */
    public function getOrCreateCustomer( $email ) {
        $user = get_user_by( 'email', $email );

        if ( ! empty( $user ) ) {
            return $user->ID;
        }

        return wp_create_user( $email, wp_generate_password( $length=12, $include_standard_special_chars=false ), $email );
    }
}
