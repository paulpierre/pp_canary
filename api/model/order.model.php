<?php

/** ===============
 *  order.model.php
 *  ===============
 */

//TODO: create unit test

class Order extends Database {

    /** ------------------------
     *  SET DATABASE TABLE NAMES
     *  ------------------------
     */
    const TABLE_NAME = 'orders';
    const TABLE_ITEM = 'items';
    const TABLE_FULFILLMENT = 'fulfillments';
    
    const TABLE_PREFIX = 'order_';

    /** ------------------------------
     *  INITIALIZE METHOD VARS TO NULL
     *  ------------------------------
     */

    public $id                  = null;
    public $customer_email      = null;
    public $customer_fn         = null;
    public $customer_ln         = null;
    public $customer_address1   = null;
    public $customer_address2   = null;
    public $customer_city       = null;
    public $customer_country    = null;
    public $customer_province   = null;
    public $customer_phone      = null;
    public $customer_zip        = null;

    public $delivery_status     = null;
    public $receipt_id          = null;
    public $tags                = null;
    public $is_ocu              = null;
    public $currency            = null;
    public $is_refunded         = null;
    public $shopify_id          = null;
    public $gateway             = null;
    public $fulfillment_status  = null;
    public $alert_status        = null;
    public $total_cost          = null;
    public $topen               = null;
    public $tclose              = null;
    public $tcreate             = null;
    public $tmodified           = null;

    public $customer_billing_fn         = null;
    public $customer_billing_ln         = null;
    public $customer_billing_address1   = null;
    public $customer_billing_address2   = null;
    public $customer_billing_city       = null;
    public $customer_billing_country    = null;
    public $customer_billing_province   = null;
    public $customer_billing_phone      = null;
    public $customer_billing_zip        = null;
    
    /** --------------------------------------------
     *  FINANCIAL STATUS - ADDED BY RAFAL 2018-03-20
     *  -------------------------------------------- */
    public $financial_status            = null;
    
    /** -------------------------------------
     *  CANCELLED - ADDED BY RAFAL 2018-03-20
     *  ------------------------------------- */
    public $tcancel                = null;


    /** -------------------------
     *  ARRAY OF CHILDREN OBJECTS
     *  -------------------------
     */

    public $order_items         = Array();
    public $order_fulfillments  = Array();


    /** -----------------------------------
     *  DYNAMIC SETTER AND GETTER FUNCTIONS
     *  -----------------------------------
     */

    function __call($method, $params) {

        $var = lcfirst(substr($method, 4));

        if (strncasecmp($method, "get_", 4) == 0) {
            return $this->$var;
        }
        if (strncasecmp($method, "set_", 4) == 0) {
            $this->$var = $params[0];
        }
    }


    /** ----------------------------------------------
     *  OBJECT SERIALIZATION TO JSON OR DATABASE ARRAY
     *  ----------------------------------------------
     */

    public function serialize_object($type=SERIALIZE_DATABASE)
    {
        $_properties = get_object_vars($this);

        foreach($_properties as $column=>$v)
        {
            if($this->$column !== null && !is_array($this->$column))
            {
                $data[self::TABLE_PREFIX . $column] = $this->$column;
            }
        }

        switch($type)
        {
            case SERIALIZE_JSON:
                return json_encode($data);
                break;

            case SERIALIZE_DATABASE:
            default:
                return $data;
                break;
        }
    }


    /** ---------------------------
     *  OBJECT CONSTRUCTOR FUNCTION
     *  ---------------------------
     */

    public function __construct($o = null)
    {

        $_properties = get_object_vars($this);

        /** ----------------------------------
         *  FETCH OBJECT INSTANCE BY OBJECT ID
         *  ----------------------------------
         */
        if($o !== null && is_numeric($o))
        {
            $o_id = $o;
            $db_conditions = Array();
            $db_conditions[self::TABLE_PREFIX . 'id'] = $o_id;

            foreach($_properties as $column=>$v)
            {
                if(!is_array($this->$column))
                    $db_columns[] = self::TABLE_PREFIX . $column;
            }

            $result = $this->db_retrieve(self::TABLE_NAME,$db_columns,$db_conditions,null,false);
            if(empty($result[0]))
                return false;
                //throw new Exception(self::TABLE_PREFIX . ' ID ' . $o_id . ' is not a valid '. self::TABLE_PREFIX . 'id.');




            /** ----------------------------------------
             *  SET OBJECT INSTANCE FROM DATABASE RESULT
             *  ----------------------------------------
             */
            $_properties = get_object_vars($this);


            foreach($_properties as $column=>$v)
            {
                if(!is_array($this->$column)) {
                    $setter = 'set_' . $column;
                    $this->$setter($result[0][self::TABLE_PREFIX . $column]);
                }
            }

        } elseif(is_array($o))
        {
            /** -----------------------------------------
             *  OBJECT INSTANCE CONSTRUCTED BY ARRAY ARGS
             *  -----------------------------------------
             */
            $_properties = get_object_vars($this);

            foreach($o as $key=>$val)
            {
                foreach($_properties as $column=>$v)
                {
                    $setter = '';
                    if($key == self::TABLE_PREFIX . $column && !is_array($this->$column))
                    {
                        $setter = 'set_' . $column;
                        $this->$setter($val);
                    }
                }
            }
        }

        /** ----------------------------------
         *  RETRIEVE ANY CHILDREN ITEM OBJECTS
         *  ----------------------------------
         */
        if($this->id !== null) $this->get_order_items();
    }


    /** ----------------------------------------
     *  SAVE THE OBJECT INSTANCE WITH NEW DATA
     *  ----------------------------------------
     */
    public function save($o = null)
    {
        $o_id = null;
        $db_columns = Array();

        if($o == null && !is_numeric($this->id))
        {
            /** ----------------------------------------------------------------
             *  CREATE A NEW RECORD IF ID ISN'T SET OR OBJECT ISN'T BEING PASSED
             *  ----------------------------------------------------------------
             */
            return $this->add_row($this);
        }

        /** -------------------------------
         *  SIMPLY UPDATE ALL INSTANCE VARS
         *  -------------------------------
         */

        if(!is_array($o) && $o instanceof Order) $o = $o->id;

        /** ----------------------------------
         *  OR PASS AN ARRAY WITH UPDATED DATA
         *  ----------------------------------
         */

        /**
         *  This method can either take an array of valid user table columns
         *  and store it, if it is not provided, it will assume to save all
         *  the properties within the object
         */

        if($o != null && is_array($o))
        {
            $o_id = $this->id;
            $data = $o;

            $_properties = get_object_vars($this);

            foreach($data as $key=>$val)
            {
                foreach($_properties as $column=>$v)
                {
                    if($key == self::TABLE_PREFIX . $column && !is_array($this->$column) && trim($val) !== trim($this->$column))
                    {
                        log_error('[' . $column . '] ' . $val . ' -> ' . $this->$column);
                        $db_columns[$key] = $val;
                    }
                }
            }

            //Nothing new to update
            if(empty($db_columns)) return false;
            log_error('DIFF: ' . print_r($db_columns,true));

            $db_columns[self::TABLE_PREFIX . 'tmodified'] = current_timestamp();

        } elseif($o != null && is_numeric($o))
        {
            $o_id = $o;
            $this->id = $o_id;


            /**
             *  No array data provided, then lets just save the properties within the object **/

            $_properties = get_object_vars($this);
            foreach($_properties as $column=>$v)
            {
                if($this->$column !== null && !is_array($v))
                    $db_columns[self::TABLE_PREFIX . $column] = $v;
            }

            $db_columns[self::TABLE_PREFIX . 'tmodified'] = current_timestamp();
        } elseif($o == null && is_numeric($this->id) && $this->id > 0 )
        {
            $db_columns = $this->serialize_object();
            $o_id = $this->id;
            $db_columns[self::TABLE_PREFIX . 'tmodified'] = current_timestamp();

        }

        if(empty($db_columns))
            throw new Exception('No data provided to update ' . self::TABLE_NAME);

        $db_conditions = array(self::TABLE_PREFIX . 'id'=>$o_id);

        try {
            $this->db_update(self::TABLE_NAME,$db_columns,$db_conditions,false);
        } catch(Exception $e) {
            log_error('Error'. $e->getCode() .': '. $e->getMessage());
        }
    }


    /** -------------------------------------
     *  GRAB ALL FULFILLMENT CHILDREN OBJECTS
     *  -------------------------------------
     */
    public function get_order_items()
    {
        if($this->id == null || $this->id <1)
            throw new Exception('Must provide object ID to get children: ' . self::TABLE_NAME);

        $o_id = $this->id;
        $db_conditions = Array();
        $db_conditions[self::TABLE_PREFIX . 'id'] = $o_id;
        $result = $this->db_retrieve(self::TABLE_ITEM,Array('item_id'),$db_conditions,null,false);
        if(empty($result))
            return false;//throw new Exception(self::TABLE_PREFIX . ' ID ' . $this->id . ' is not a valid '. self::TABLE_PREFIX . 'id.');
        else {
            foreach($result as $o)
            {
                log_error('checking child object: ' . print_r($o,true));
                $item_id = $o['item_id'];
                $this->order_items[] = new Item($item_id);
            }
        }
        return $result;
    }


    /** --------------------------------
     *  GET OBJECT BY SHOPIFY IDENTIFIER
     *  --------------------------------
     */
    public function fetch_order_by_order_shopify_id($id)
    {
        $db_conditions = Array(
            'order_shopify_id'=>intval($id)
        );
        $db_columns = Array('order_shopify_id','order_id');
        $result = $this->db_retrieve(self::TABLE_NAME,$db_columns,$db_conditions,null,false);
        if(empty($result)) return false;
        else return new Order($result[0]['order_id']);
    }


    /** --------------------------------
     *  ADD A NEW OBJECT TO THE DATABASE
     *  --------------------------------
     */
    public function add_row($o = null)
    {
        /**
         *  $order should be a order object being passed
         */

        if($o instanceof Order)
        {
            $db_columns =  $o->serialize_object();
            $db_columns[self::TABLE_PREFIX . 'tmodified'] = current_timestamp();
            $db_columns[self::TABLE_PREFIX . 'tcreate'] = current_timestamp();

            if(!isset($db_columns[self::TABLE_PREFIX . 'id'])) $db_columns[self::TABLE_PREFIX . 'id'] = $this->id;
        } else {
            throw new Exception('Not a valid ' . self::TABLE_NAME .' object!' . print_r($o,true));
        }

        if(isset($db_columns[self::TABLE_PREFIX . 'id'])) unset($db_columns[self::TABLE_PREFIX . 'id']);

        try {
            $insert_id = $this->db_create(self::TABLE_NAME,$db_columns);
            $this->set_id($insert_id);
            return $insert_id;

        } catch(Exception $e) {
            log_error('Error'. $e->getCode() .': '. $e->getMessage());
        }
        return false;
    }
}




