<?php

/** ==============
 *  item.model.php
 *  ==============
 */

class Item extends Database {

    /** ------------------------
     *  SET DATABASE TABLE NAMES
     *  ------------------------
     */
    const TABLE_NAME        = 'items';
    const TABLE_ORDERS      = 'orders';
    const TABLE_FULFILLMENT = 'fulfillments';
    
    const TABLE_PREFIX      = 'item_';




    /** ------------------------------
     *  INITIALIZE METHOD VARS TO NULL
     *  ------------------------------
     */

    public $id                  = null;
    public $order_id            = null;
    public $order_shopify_id    = null;
    public $shopify_id          = null;
    public $quantity            = null;
    public $shopify_product_id  = null;
    public $shopify_variant_id  = null;
    public $sku                 = null;
    public $name                = null;
    public $price               = null;
    public $is_refunded         = null;
    public $is_fulfilled        = null;
    public $refund_tcreate      = null;
    public $tcreate             = null;
    public $tmodified           = null;

    public $FK_PREFIX_ARRAY      = Array('order_','fulfillment_');

    /** -------------------------
     *  ARRAY OF CHILDREN OBJECTS
     *  -------------------------
     */

    public $item_fulfillments  = Array();

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
                if(is_array_prefix($this->FK_PREFIX_ARRAY,$column))
                {
                    $data[$column] = $this->$column;
                }else
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
                    if(is_array_prefix($this->FK_PREFIX_ARRAY,$column))
                        $db_columns[] = $column ;
                    else
                        $db_columns[] = self::TABLE_PREFIX . $column;
            }

            $result = $this->db_retrieve(self::TABLE_NAME,$db_columns,$db_conditions,null,false);
            if(empty($result[0]))
                return false;//throw new Exception(self::TABLE_PREFIX . ' ID ' . $o_id . ' is not a valid '. self::TABLE_PREFIX . 'id.');




            /** ----------------------------------------
             *  SET OBJECT INSTANCE FROM DATABASE RESULT
             *  ----------------------------------------
             */
            $_properties = get_object_vars($this);


            foreach($_properties as $column=>$v)
            {
                if(!is_array($this->$column)) {
                    $setter = 'set_' . $column;
                    if(is_array_prefix($this->FK_PREFIX_ARRAY,$column))
                        $this->$setter($result[0][ $column]);
                    else
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
                    if(($key == self::TABLE_PREFIX . $column || $key == $column ) && !is_array($this->$column))
                    {
                        $setter = 'set_' . $column;
                        $this->$setter($val);
                    }
                }
            }
        }


        /** -----------------------------------------
         *  RETRIEVE ANY CHILDREN FULFILLMENT OBJECTS
         *  -----------------------------------------
         */
        if($this->id !== null) $this->get_item_fulfillments();

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

        if(!is_array($o) && $o instanceof Item) $o = $o->id;

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
                        log_error('col [' . $column . '] ' . $val . ' -> ' . $this->$column);
                        $db_columns[$key] = $val;
                    }
                }
            }

            //Nothing new to update
            if(empty($db_columns)) return false;

            $db_columns[self::TABLE_PREFIX . 'tmodified'] = current_timestamp();
            log_error('DIFF: ' . print_r($db_columns,true));

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
                    if(is_array_prefix($this->FK_PREFIX_ARRAY,$column))
                        $db_columns[$column] = $v;
                    else
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
    public function get_item_fulfillments()
    {
        if($this->id == null || $this->id <1)
            throw new Exception('Must provide object ID to get children: ' . self::TABLE_NAME);

        $o_id = $this->id;
        $db_conditions = Array();
        $db_conditions[self::TABLE_PREFIX . 'id'] = $o_id;
        $result = $this->db_retrieve(self::TABLE_FULFILLMENT,Array('fulfillment_id'),$db_conditions,null,false);
        if(empty($result))
            return false;//throw new Exception(self::TABLE_PREFIX . ' ID ' . $this->id . ' is not a valid '. self::TABLE_PREFIX . 'id.');
        else {
            foreach($result as $o)
            {
                $fulfillment_id = $o['fulfillment_id'];
                $this->item_fulfillments[] = new Fulfillment($fulfillment_id);
            }
        }
        return $result;
    }

    /** --------------------------------
     *  GET OBJECT BY SHOPIFY IDENTIFIER
     *  --------------------------------
     */
    public function fetch_item_by_shopify_item_id($id)
    {
        $db_conditions = Array(
            'item_shopify_id'=>$id
        );
        $db_columns = Array('item_shopify_id','item_id');
        $result = $this->db_retrieve(self::TABLE_NAME,$db_columns,$db_conditions,null,false);
        if(empty($result)) return false;
        else return new Item($result[0]['item_id']);
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

        if($o instanceof Item)
        {
            $db_columns =  $o->serialize_object();
            $db_columns[self::TABLE_PREFIX . 'tmodified'] = current_timestamp();
            $db_columns[self::TABLE_PREFIX . 'tcreate'] = current_timestamp();
            if(!isset($db_columns[self::TABLE_PREFIX . 'id'])) $db_columns[self::TABLE_PREFIX . 'id'] = $this->id;
        } else {
            throw new Exception('Not a valid ' . self::TABLE_NAME .' object!' . print_r($o,true));
        }

        if(isset($db_columns[self::TABLE_PREFIX . 'id']) || $db_columns[self::TABLE_PREFIX . 'id'] == null) unset($db_columns[self::TABLE_PREFIX . 'id']);

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




