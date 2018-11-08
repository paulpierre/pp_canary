<?php

/** =====================
 *  fulfillment.model.php
 *  =====================
 */

class Fulfillment extends Database {

    /** ------------------------
     *  SET DATABASE TABLE NAMES
     *  ------------------------
     */
    const TABLE_NAME        = 'fulfillments';
    const TABLE_ITEM        = 'items';
    const TABLE_ORDER       = 'orders';
    
    const TABLE_PREFIX      = 'fulfillment_';



    /** ------------------------------
     *  INITIALIZE METHOD VARS TO NULL
     *  ------------------------------
     */

    public $id                                  = null;
    public $order_id                            = null;
    public $order_shopify_id                    = null;
    public $shopify_id                          = null;
    public $shipment_status                     = null; //this is for shopify's unique status system

    public $tracking_country_from               = null;
    public $tracking_country_to                 = null;
    public $tracking_carrier_from               = null;
    public $tracking_carrier_to                 = null;

    public $tracking_number                     = null;
    public $tracking_number_tcreate             = null;
    public $tracking_company                    = null;
    public $tracking_url                        = null;
    public $is_tracking                         = null;
    public $alert_status                        = null;
    public $delivery_status                     = null; //this is the official one we track internally
    public $status_delivered_tcreate            = null;
    public $status_confirmed_tcreate            = null;
    public $status_in_transit_tcreate           = null;
    public $status_out_for_delivery_tcreate     = null;
    public $status_failure_tcreate              = null;
    public $status_not_found_tcreate            = null;
    public $status_customer_pickup_tcreate      = null;
    public $status_alert_tcreate                = null;
    public $status_expired_tcreate              = null;

    public $tracking_last_date                  = null;
    public $tracking_last_status_text           = null;

    public $vendor_id                         = null;

    public $topen                               = null;

    public $tcheck                              = null;
    public $tcreate                             = null;
    public $tmodified                           = null;

    public $FK_PREFIX_ARRAY      = Array('order_','item_');

    /** -------------------------
     *  ARRAY OF CHILDREN OBJECTS
     *  -------------------------
     */



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
                }
                else {
                    $data[self::TABLE_PREFIX . $column] = $this->$column;

                }
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
                {
                    if(is_array_prefix($this->FK_PREFIX_ARRAY,$column))
                        $db_columns[] = $column;
                    else
                        $db_columns[] = self::TABLE_PREFIX . $column;
                }
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
                    //TODO: consider how to fix this with foreign key issues
                    $setter = '';
                    //if($key == self::TABLE_PREFIX . $column && !is_array($this->$column))
                    if(($key == self::TABLE_PREFIX . $column || $key == $column ) && !is_array($this->$column))

                    {
                        $setter = 'set_' . $column;
                        $this->$setter($val);
                    }
                }
            }
        }
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

        if(!is_array($o) && $o instanceof Fulfillment) $o = $o->id;

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
                log_error(PHP_EOL . '####KEY: ' . $key.PHP_EOL);
                foreach($_properties as $column=>$v)
                {
                    if($key == (self::TABLE_PREFIX . $column) && !is_array($this->$column) && trim($val) !== trim($this->$column))
                    {
                        $db_columns[$key] = $val;

                        /** ----------------------------
                         *  LETS CHECK AND UPDATE STATES
                         *  ---------------------------- */
                        switch($key)
                        {
                            case 'tracking_number_tcreate':
                                $db_columns[self::TABLE_PREFIX . 'tracking_number_tcreate'] = current_timestamp();
                                break;

                            case 'alert_status':
                                switch($val)
                                {


                                    case NOTIFICATION_STATUS_RESOLVED:
                                    break;
                                    case NOTIFICATION_STATUS_EXTENDED_NOT_FOUND:  //Courier perhaps lost the package
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 0;
                                        break;
                                    case NOTIFICATION_STATUS_EXTENDED_IN_TRANSIT: //Item is likely stuck in customs
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 0;
                                        break;
                                    case NOTIFICATION_STATUS_CUSTOMER_PICKUP:     //Email customers to pick up their item at the post office
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 0;
                                    break;
                                    case NOTIFICATION_STATUS_DELIVERY_FAILURE:  //Email customers to call their local post office
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 0;
                                    break;
                                    case NOTIFICATION_STATUS_ALERT_CUSTOMS://Inform suppliers that items was likely rejected by customs
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 0;
                                    break;
                                }
                            break;

                            case 'delivery_status':

                                //If the shipment status changes, lets updated the appropriate timestamp columns
                                switch(intval($val))
                                {
                                    case DELIVERY_STATUS_DELIVERED:
                                        $db_columns[self::TABLE_PREFIX . 'status_delivered_tcreate'] = current_timestamp();
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 0;
                                        break;

                                    case DELIVERY_STATUS_CONFIRMED:
                                        $db_columns[self::TABLE_PREFIX . 'status_confirmed_tcreate'] = current_timestamp();
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 1;
                                        break;

                                    case DELIVERY_STATUS_IN_TRANSIT:
                                        $db_columns[self::TABLE_PREFIX . 'status_in_transit_tcreate'] = current_timestamp();
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 1;
                                        break;

                                    case DELIVERY_STATUS_OUT_FOR_DELIVERY:
                                        $db_columns[self::TABLE_PREFIX . 'status_out_for_delivery_tcreate'] = current_timestamp();
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 1;
                                        break;

                                    case DELIVERY_STATUS_FAILURE:
                                        $db_columns[self::TABLE_PREFIX . 'status_failure_tcreate'] = current_timestamp();
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 1;
                                        break;

                                    case DELIVERY_STATUS_NOT_FOUND:
                                        $db_columns[self::TABLE_PREFIX . 'status_not_found_tcreate'] = current_timestamp();
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 1;
                                        break;

                                    case DELIVERY_STATUS_PICKUP:
                                        $db_columns[self::TABLE_PREFIX . 'status_customer_pickup_tcreate'] = current_timestamp();
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 1;
                                        break;

                                    case DELIVERY_STATUS_ALERT:
                                        $db_columns[self::TABLE_PREFIX . 'status_alert_tcreate'] = current_timestamp();
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 0;
                                        break;

                                    case DELIVERY_STATUS_EXPIRED:
                                        $db_columns[self::TABLE_PREFIX . 'status_expired_tcreate'] = current_timestamp();
                                        $db_columns[self::TABLE_PREFIX . 'is_tracking'] = 0;
                                        break;
                                }

                            break;
                        }
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


    /** --------------------------------
     *  GET OBJECT BY SHOPIFY IDENTIFIER
     *  --------------------------------
     */
    public function fetch_fulfillment_by_shopify_fulfillment_id($id)
    {
        $db_conditions = Array(
            'fulfillment_shopify_id'=>$id
        );
        $db_columns = Array('fulfillment_id');
        $result = $this->db_retrieve(self::TABLE_NAME,$db_columns,$db_conditions,null,false);
        if(empty($result)) return false;
        else return new Fulfillment(intval($result[0]['fulfillment_id']));
    }

    /** --------------------------------
     *  GET OBJECT BY SHOPIFY IDENTIFIER
     *  --------------------------------
     */
    public function fetch_fulfillment_by_tracking_number($id)
    {
        $db_conditions = Array(
            'fulfillment_tracking_number'=>$id
        );
        $db_columns = Array('fulfillment_id');
        $result = $this->db_retrieve(self::TABLE_NAME,$db_columns,$db_conditions,null,false);
        if(empty($result)) return false;
        else return new Fulfillment(intval($result[0]['fulfillment_id']));
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

        if($o instanceof Fulfillment)
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




