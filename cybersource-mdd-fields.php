<?php

/**
 * Cybersource Merchant Defined Data Fields (MDD) Wordpress theme functions.php Implementation.
 *
 */

add_filter( 'cybersource_add_signed_field_names', 'cybersource_add_mdd_fields' );

function cybersource_add_mdd_fields(){

	global $woocommerce;

	$consumer_id = get_current_user_id();
	$previous_customer = ( $consumer_id == 0 ? 'NO' : 'YES' ); 
	

	// Get Cart Items
	$cart_items = $woocommerce->cart->get_cart();	

	$cb_items = array();

	if( ! empty( $cart_items ) ){

		if( count($cart_items) == 1 ){
			
			$x = 0;
			foreach( $cart_items  as $values ){
				
				if( $values[ 'product_id' ] ){
					$product = new WC_Product( $values['product_id']);
					$products_cats = $product->get_category_ids();
				} else {
					$product = new WC_Product( $values['variation_id']);
					$products_cats = $product->get_category_ids();
				}
				
				
				$_product = $values['data']->post;

				$cb_items[ 'merchant_defined_data3' ] = $_product->post_title;

				if( is_array($products_cats) && ! empty($products_cats)){
					$c = array();
					foreach ($products_cats as $value) {
						$c[] = get_cat_name( $value );	
					}

					$products_cats = implode( ',', $c );
				} else {
					$products_cats = '';
				}

				$cb_items[ 'merchant_defined_data4' ] = $products_cats;

				$x++;
			}	

		} else {


			$x = 0;
			foreach( $cart_items  as $values ){
				
				if( $values[ 'product_id' ] ){
					$product = new WC_Product( $values['product_id']);
					$products_cats = $product->get_category_ids();
				} else {
					$product = new WC_Product( $values['variation_id']);
					$products_cats = $product->get_category_ids();
				}
				
				
				$_product 				= $values['data']->post;
				
				$mdd_product[ 'name' ][]  = $_product->post_title; 

				if( ! empty($products_cats) && is_array($products_cats)){
					foreach ( $products_cats as $key => $value) {
						$mdd_product[ 'cats' ][]  = $value; 
					}
				} else {
					$mdd_product[ 'cats' ]  = $products_cats; 
				}
				

				$x++;
			}

			

			if( ! empty( $mdd_product[ 'name' ] ) && is_array($mdd_product[ 'name' ])){
				$cb_items[ 'merchant_defined_data3' ] = implode( ',', $mdd_product[ 'name' ] );
			}

			if( ! empty($mdd_product[ 'cats' ]) && is_array($mdd_product[ 'cats' ] )){
				$c = array();

				foreach ($mdd_product[ 'cats' ] as  $value) {
					$c[] = get_cat_name( $value );
				}
				$cb_items[ 'merchant_defined_data4' ] = implode( ',', $c);
			}
			
		}

	}


	$shipping_class = array_values($cart_items);
	
	//echo "<pre>"; print_r($shipping_class); echo "</pre>";

	if( ! empty( $shipping_class)){
		$shipping_class = $shipping_class[0]['data']->get_shipping_class();
		
		if( empty($shipping_class) ){
				$shipping_class = WC()->session->get('chosen_shipping_methods');
				$shipping_class = $shipping_class[0];
		}
		
		
		
	} else {
		$shipping_class = 'No shipping method available';
	}
	
	$mdd1 =  array(
			'consumer_id' 			  =>  $consumer_id, // Consumer ID
			'merchant_defined_data1'  => __( 'WC', 'woocommerce'  ),  // Chanel of Operation
	  		'merchant_defined_data2'  => __( 'YES', 'woocommerce'  ), // 3D secure registration
	  		'merchant_defined_data5'  =>  $previous_customer, // Previous Customer 
	  		'merchant_defined_data6'  =>  $shipping_class, // Shipping Method
	  		'merchant_defined_data7'  =>  WC()->cart->get_cart_contents_count(), // Number of Items Sold
	  		'merchant_defined_data8'  =>  $woocommerce->customer->get_country(), // Product Shipping Country
	  		'merchant_defined_data20' => __( 'NO', 'woocommerce'  ), // VIP Customer
	  		
		);

	$mdd = array_merge( $mdd1, $cb_items );

	return $mdd;

}
