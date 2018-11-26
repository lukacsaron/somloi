<?php
/**
 * Order Invoices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<style type="text/css">
    body {
        font-family:"Helvetica Neue", Helvetica, Arial, Verdana, sans-serif;
    }

    h1 span {
        font-size:0.75em;
    }

    h2 {
        color: #333;
    }
    .no-page-break {
        page-break-after: avoid;
    }

    #wrapper {
        margin:0 auto;
        width:95%;
        page-break-after: always;
    }

    #wrapper_last {
        margin:0 auto;
        width:95%;
        page-break-after: avoid;
    }

    .address{
        width:98%;
        border-top:1px;
        border-right:1px;
        margin:1em auto;
        border-collapse:collapse;
    }
    
    .address_border{
        border-bottom:1px;
        border-left:1px ;
        padding:.2em 1em;
        text-align:left;
    }
   
    table {
        width:98%;
        border-top:1px solid #e5eff8;
        border-right:1px solid #e5eff8;
        margin:1em auto;
        border-collapse:collapse;
        font-size:10pt;
    }
    td {
        border-bottom:1px solid #e5eff8;
        border-left:1px solid #e5eff8;
        padding:.3em 1em;
        text-align:center;
    }

    tr.odd td,
    tr.odd .column1 {
        background:#f4f9fe url(background.gif) no-repeat;
    }
    .column1 {
        background:#f4f9fe;
    }

    thead th {
        background:#f4f9fe;
        text-align:center;
        font:bold 1.2em/2em "Century Gothic","Trebuchet MS",Arial,Helvetica,sans-serif;
    }
    .sm_datagrid {

        position: relative;
        top:-30pt;
    }
    .producthead{ 
        text-align: left;
    }
    .pricehead{
        text-align: right;
    }
    .sm_address_div{
        position: relative;
        left:28pt;
    }
    .sm_email_span{
        position: relative;
        left:10pt;
    }

</style>
<?php 
$counter = 0;
foreach ($purchase_id_arr as $purchase_id_value){
    $order = new WC_Order($purchase_id_value);
    $order_data = ( $sm_is_woo30 ) ? $order->get_data() : $order;

    $order_date = ( $sm_is_woo30 ) ? $order->get_date_created()->date('Y-m-d H:i:s') : $order->order_date;
    $billing_email = ( $sm_is_woo30 ) ? $order_data['billing']['email'] : $order->billing_email;
    $billing_phone = ( $sm_is_woo30 ) ? $order_data['billing']['phone'] : $order->billing_phone;
    $customer_note = ( $sm_is_woo30 ) ? $order_data['customer_note'] : $order->customer_note;
    $order_discount = ( $sm_is_woo30 ) ? $order_data['discount_total'] : $order->order_discount;
    $order_total = ( $sm_is_woo30 ) ? $order_data['total'] : $order->order_total;
    $payment_method_title = ( $sm_is_woo30 ) ? $order_data['payment_method_title'] : $order->payment_method_title;

    $date_format = get_option('date_format');

    if (is_plugin_active ( 'woocommerce-sequential-order-numbers-pro/woocommerce-sequential-order-numbers.php' )) {
        $purchase_display_id = (isset($order_data->order_custom_fields['_order_number_formatted'][0])) ? $order_data->order_custom_fields['_order_number_formatted'][0] : $purchase_id_value;
    } else {
        $purchase_display_id = $purchase_id_value;
    }

    $counter++;
    if ( count( $purchase_id_arr ) == $counter ) {
        echo '<div id="wrapper_last">';
    } else {
        echo '<div id="wrapper">';
    }
    echo $smart_manager_beta->smart_manager_print_logo();
    echo '<div style="margin-top:-0.8em;">';
    if (get_option('smart_manager_company_logo') == '') {
        echo '<h4 style="font:bold 1.2em/2em "Century Gothic","Trebuchet MS",Arial,Helvetica,sans-serif;
                position:relative; 12pt;">&nbsp; '.get_bloginfo( 'name' ).'</h4>';
    }
    echo '</br> <table class="address" style="position:relative; top:-22pt; left:-35pt;">';
    echo '<tr><td class="address_border" colspan="2" valign="top" width="50%"><span style="position:relative; left:27pt; top:10pt;">
            <b>Order # '.$purchase_display_id.' - '.date($date_format, strtotime($order_date)).'</b></span><br/></td></tr>';
    echo '<tr><td class="address_border" width="35%" align="center"><br/><div class="sm_address_div">';
    
    $formatted_billing_address = $order->get_formatted_billing_address();
    if( $formatted_billing_address != '' ) {
        echo '<b>'.__('Billing Address', $sm_text_domain).'</b><p>';
        echo $formatted_billing_address; 
        echo '</p></td>';
    }
    
    $formatted_shipping_address = $order->get_formatted_shipping_address();
    if( $formatted_shipping_address != '' ) {
        echo '<td class="address_border" width="30%"><br/><div style="position:relative; top:3pt;"><b>'.__('Shipping Address', $sm_text_domain).'</b><p>';
        echo $formatted_shipping_address;
        echo '</p></div></td>';
    }
                
    echo '</tr>';
    echo '<tr><td colspan="2" class="address_border"><span class="sm_email_span"><table class="address"><tr><td colspan="2" class="address_border" >
            <b>'.__('Email id', $sm_text_domain).':</b> '.$billing_email.'</td></tr>
            <tr><td class="address_border"><b>'.__('Tel', $sm_text_domain).' :</b> '.$billing_phone.'</td></tr></table> </span></td></tr>';
    echo '</table>';
    echo '<div class="sm_datagrid"><table><tr class="column1">
            <td class="producthead">'.__('Product', $sm_text_domain).'</td><td>'.__('SKU', $sm_text_domain).'</td>
            <td>'.__('Quantity', $sm_text_domain).'</td><td class="pricehead">'.__('Price', $sm_text_domain).'</td></tr>';
            
    $total_order = 0;

    foreach($order->get_items() as $order_item) {
        $_product = $order->get_product_from_item( $order_item );
        $_product_data = ($sm_is_woo30) ? $_product->get_data() : $_product;

        $item = ( $sm_is_woo30 ) ? $order_item->get_data() : $order_item;

        if( $sm_is_woo30 ) {
            $formatted_variation = (!empty($_product_data['attributes']) && $_product->post_type == 'product_variation') ? wc_get_formatted_variation($_product_data['attributes'], true) : '';
        } else {
            $formatted_variation = (!empty($_product)) ? woocommerce_get_formatted_variation( $_product->variation_data, true ) : '';
        }

        $sku = $variation = '';
        $qty = ( $sm_is_woo30 ) ? $item['quantity'] : $item['item_meta']['_qty'][0];
        $sku = (!empty($_product)) ? $_product->get_sku() : '';
        $variation = ( !empty( $formatted_variation ) ) ? ' (' . $formatted_variation . ')' : '';
        $item_total = ($sm_is_woo30) ? $order_item->get_total() : $order_item['line_total'];
        $total_order += $item_total;
        echo '<tr><td class="producthead">';
        echo $item['name'] . $variation;
        echo '</td><td>'.$sku.'</td><td>';
        echo $qty;
        echo '</td><td class="pricehead">';
        echo ($sm_is_woo30) ? wc_price($item_total) : woocommerce_price($item_total);
        echo '</td></tr>';    
    }

    echo '<tr><td colspan="2" rowspan="5" class="address_border" valign="top"><br/>
            <i>'.(($customer_note != '')? __('Order Notes', $sm_text_domain).' : ' .$customer_note:'').'</i></td><td style="text-align:right;" class="address_border" valign="top">
            <b>Subtotal </b></td><td class="pricehead">'.$order->get_subtotal_to_display().'</td></tr>';
    echo '<tr><td style="text-align:right;" class="address_border"><b>'.__('Shipping', $sm_text_domain).' </b></td><td class="pricehead">'.$order->get_shipping_to_display().'</td></tr>';
    
    if ($order_discount > 0) {
        $order_discount = ($sm_is_woo30) ? wc_price($order_discount) : woocommerce_price($order_discount);
        echo '<tr><td style="text-align:right;" class="address_border"><b>'.__('Order Discount', $sm_text_domain).' </b></td>';
        echo '<td class="pricehead">'.$order_discount.'</td></tr>';
    }

    $order_tax = ($sm_is_woo30) ? wc_price($order->get_total_tax()) : woocommerce_price($order->get_total_tax());
    $order_total = ($sm_is_woo30) ? wc_price($order_total) : woocommerce_price($order_total);

    echo '<tr><td style="text-align:right;" class="address_border"><b>'.__('Tax', $sm_text_domain).' </b></td><td class="pricehead">'.$order_tax.'</td></tr>';
    echo '<tr><td class="column1" style="text-align:right;"><b>'.__('Total', $sm_text_domain).' </b></td><td class="column1" style="text-align:right;">'.$order_total.' -via '.$payment_method_title.'</td></tr>';
    echo '</table></div></div></div>';
}
