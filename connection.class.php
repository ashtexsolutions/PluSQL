<?php
    class Connection
    {
        private $credentials;
        private $link;

        public function __construct($host,$user,$password,$dbname)
        {
            $this->credentials = func_get_args();
            
            if(class_exists('mysqli'))
                $this->link = new mysqli($host,$user,$password);
            else
                $this->link = mysql_connect($host,$user,$password);
        }
        
        public function connect()
        {
            if($this->link instanceof mysqli)
                $this->link->select_db($this->credentials[Anorm::NAME]) or die($this->link->error);
            else
                mysql_select_db($this->credentials[Anorm::NAME],$this->link) or die(mysql_error());
        }
        
        public function query($sql)
        {
            return new AnormQuery($sql,$this->link);
        }
    }
