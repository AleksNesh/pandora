<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cart
 *
 * @author kienkun1990
 */
class Magestore_Giftwrap_Block_Checkout_Cart extends Mage_Checkout_Block_Cart {

    //put your code here
    public function _construct() {
        parent::_construct();
        $this->setTemplate('giftwrap/cart_page.phtml');
    }

}

?>
