<?php
/**
* cat-db.inc.php (Classe per gestire interazini con mysqli del sito)
*
* +----------------------------------------------------------------------+
* |                                                                      |
* | web-sytem 3.0rc : Web Site Management System                         |
* |                                                                      |
* +----------------------------------------------------------------------+
* | Copyright (c) davcaffa@gmail.com                                     |
* +----------------------------------------------------------------------+
* | Authors: Davide Caffaratti <davcaffa@gmail.com>                      |
* |                                                                      |
* +----------------------------------------------------------------------+
*
*/

define('OBJECT', 'OBJECT_DB');
define('ARRAY_A', 'ARRAY_A');
define('ARRAY_N', 'ARRAY_N');

/**
* @package Cat_Db
* @desc Cat_Db DB Class 
* @author Justin Vincent <justin@visunet.ie>
* @author Davide Caffaratti <davcaffa@gmail.com>     
* @version 1.0
* ORIGINAL CODE FROM:
* Justin Vincent (justin@visunet.ie)
* http://php.justinvincent.com
*/
class Cat_Db
{
    /**
    * @desc contiene array dati ultima query eseguita
    * @access private
    * @var arrary $last_query    
    */
    private $last_query = null;
    /**
    * @desc puntatore alla connessione
    * @access private
    * @var resource $dbh
    */
    private $dbh = null;
    /**
    * @desc results della query
    * @access private
    * @var object $last_result
    */
    private $last_result = null;
    /**
    * @desc puntatore alla connessione
    * @access private
    * @var string $paginationTitle
    */
    private $paginationTitle = null;
    /**
    * @desc Se abbiamo una transazione in corso lo segnala
    * @access private
    * @var boolean $haveTransaction
    */
    private $haveTransaction = false;
    /**
    * @desc Se abbiamo un look in corso lo segnala
    * @access private
    * @var boolean $haveTransaction
    */
    private $haveLook = false;
    
    /**
    * @desc Costruttore della classe
    * @access public 
    * @return void
    */
    public function __construct()
    {
        if (!$this->dbh) {
            // eseguo la connessione e setto dbh
            if (!$this->dbh = mysqli_connect(CAT_DBHOST, CAT_DBUSER, CAT_DBPASS)) {
                trigger_error('Connessione al database non riuscita', E_USER_ERROR);
                exit;
            }
            if (!mysqli_select_db($this->dbh, CAT_DB)) {
                trigger_error('Database *'.CAT_DB.'* incorretto !!', E_USER_ERROR);
                exit;
            }
            if (CAT_DB_USE_UTF_8) {
                mysqli_set_charset($this->dbh, "utf8");
            }
        }
    }
    /**
    * @desc Pubblico distruttore della classe
    * @access public
    */
    public function __destruct()
    {
        // flusho dati query
        $this->flush();
        if ($this->dbh) {            
            // Eseguo sblocco tabelle per evitare di lasciarne bloccate
            if ($this->haveLook) {
                $this->unlockTable();
            }
            // chiudo connessione
            @mysqli_close($this->dbh);
        }
        // setto a null il link alla connessione
        $this->dbh = null;
        $this->last_result = null;
        $this->last_query = null;
        $this->paginationTitle = null;
        $this->haveTransaction = null;
        $this->haveLook = null;
    }
    /**
    * @desc fluscha la query cachata dalla classe
    * @access public
    * @return bool true
    */
    public function flush() 
    {
        $this->last_result = null;
        $this->last_query = null;
        $this->paginationTitle = null;
        return true;
    }
    /**
    * @desc esegue una query di base
    * @access public
    * @param string $query Stringa di testo con sql da usare
    * @param boolean $insert_useLastID Se settato a true usa la funzione mysqli_insert_id per ritornare ultimo id inserito, altrimenti ritorna true
    * @return mixed
    */
    public function query($query, $insert_useLastID=true)
    {      
        // initialise return
        $return_val = 0;
        $this->flush();
        
        // Keep track of the last query for debug..
        $this->last_query = $query;

        // Pingo il server e riconnetto se la connessione è caduta
        if (!mysqli_ping($this->dbh)) {
            $this->clearLockTransaction();
            trigger_error('Connessione al database durante una query non riuscita !! * '.$query.' *', E_USER_ERROR);
            exit;
        }
        else {
            // Eseguo la query
            $this->result = mysqli_query($this->dbh, $query);
        }

        // Controllo errore query
        $sqlError = mysqli_error($this->dbh);

        // If there is an error then take note of it..
        if ($sqlError) {
            header('HTTP/1.1 500 Server Error.'); 
            $this->clearLockTransaction();
            trigger_error('Errore inprevisto in una query | '.$sqlError.' | '.$query.' | nel file '.$_SERVER['SCRIPT_FILENAME'].' | '.$this->last_query, E_USER_ERROR);
            exit;
        }

        if ( preg_match("/^\\s*(insert|delete|update|replace|lock|unlock|truncate|optimize|drop) /i",$query) ) {
            // Take note of the insert_id
            if ( preg_match("/^\\s*(insert|replace) /i",$query) ) { 
                // Inserted id   
                if ($insert_useLastID) {
                    $this->insert_id = mysqli_insert_id($this->dbh);
                    $return_val = $this->insert_id;
                }
                else {
                    // Return number of rows affected
                    $return_val = true;
                }
            }
            // Take note of the lock unlock
            else if ( preg_match("/^\\s*(lock|unlock) /i",$query) ) {
                $return_val = true;
            }
            else {
            	$this->rows_affected = mysqli_affected_rows($this->dbh);
                // Return number of rows affected
                $return_val = $this->rows_affected;
            }
        } 
        // Take note of the transaction function
        else if ( preg_match("/^(start transaction|set autocommit|rollback|commit|set autocommit')/i",$query) ) { 
            $return_val = true;
        }
        else {
            $i = 0;

            $num_rows = 0;
            while ( $row = mysqli_fetch_object($this->result) ) {
                $this->last_result[$num_rows] = $row;
                $num_rows++;
            }
            mysqli_free_result($this->result);

            // Log number of rows the query returned
            $this->num_rows = $num_rows;
            // Return number of rows selected
            $return_val = $this->num_rows;
        }

        return $return_val;
    }
    /**
    * @desc Get one variable from the DB
    * @access public
    */
    public function get_var($query=null, $x = 0, $y = 0) 
    {
        if ($query) {
            $this->query($query);
        }
        
        // Extract var out of cached results based x,y vals
        if (isset($this->last_result) && $this->last_result[$y]) {
            $values = array_values(get_object_vars($this->last_result[$y]));
            // If there is a value return it else return null
            return (isset($values[$x]) && $values[$x]!=='') ? $values[$x] : null;
        }
        else {
            return $this->last_result;
        }
    }
    /**
    * @desc Get one row from the DB 
    * @access public
    */
    public function get_row($query = null, $output = 'OBJECT_DB', $y = 0) 
    {
        if ($query) {
            $this->query($query);
        }
        
        if ($this->last_result === null) {
            return $this->last_result;
        }
        elseif ( $output === 'OBJECT_DB' ) {
            return $this->last_result[$y] ? $this->last_result[$y] : null;
        } 
        elseif ( $output === 'ARRAY_A' ) { 
            return ($this->last_result[$y]) ? get_object_vars($this->last_result[$y]) : null;
        } 
        elseif ( $output === 'ARRAY_N' ) {
            return $this->last_result[$y] ? array_values(get_object_vars($this->last_result[$y])) : null;
        } 
        else {
            trigger_error("\$db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT_DB, ARRAY_A, ARRAY_N", E_USER_ERROR);
            exit;
        }
    }
    /**
    * @desc Functione per aggiungere slash corretti prima di inserire nel db
    * @param string $string La stringa da escapare
    * @access public
    */
    public function escape($string)
    {
        return mysqli_real_escape_string($this->dbh, $string);
    }
    /**
    * @desc Return the the query as a result set
    * @access public
    */
    public function get_results($query = null, $output = 'OBJECT_DB') 
    {        
        if ($query) {
            $this->query($query);
        }

        // Send back array of objects. Each row is an object
        if ( $output == 'OBJECT_DB' ) {
            return $this->last_result;
        } elseif ( $output == 'ARRAY_A' || $output == 'ARRAY_N' ) {
            if ( $this->last_result ) {
                $i = 0;
                foreach( $this->last_result as $row ) {
                    $new_array[$i] = (array) $row;
                    if ( $output == ARRAY_N ) {
                        $new_array[$i] = array_values($new_array[$i]);
                    }
                    $i++;
                }
                return $new_array;
            } else {
                return null;
            }
        }
    }
    /**
    * @desc setta il result paginato e setta parametri paginazione
    * @access public
    * @uses Cat_Pager class
    * @see file cat-pagination.inc.php
    * @param string $query Query sql
    * @param string $page_url Url della pagina
    * @param integer $max_for_page Max risultati per pagina
    * @param boolean $mod_rewrite Se settata a true setta i link in base al modrewrite
    * @param integer $ofset Usato solo se stiamo usando mod rewrite nella paginazione
    * @param string $results_type default 'ARRAY_A'
    * @return array ('results'=>(array)actual_recordset,'pagination'=>(string)actual_pagination,'paginationFooter'=>(string)actual_pagination_footer)
    */
    public function get_results_pagination($query, $page_url=false, $max_for_page=20, $mod_rewrite=false, $ofset=1, $results_type='ARRAY_A')
    {
        // Se non abbiamo la libreria caricata la carico
        if (!defined('CAT_PAGER')) {
            include(INCLUDE_LIB.'cat-pagination.inc.php');
        }

        // Inizializzo classe pager
        $pager = new Cat_Pager($max_for_page, $page_url, $mod_rewrite, $ofset);

        // aggiungo query cache e found row
        $sqlQueryCache = 'SELECT SQL_CALC_FOUND_ROWS SQL_CACHE '.substr($query, 7);

        // Formo la query con il limite
        $sql = $sqlQueryCache." LIMIT ".$pager->get_start().", ".$max_for_page;

        // se dobbiamo tornare solo un risultato alla volta
        $results = ($max_for_page == 1) ? $this->get_row($sql, $results_type) : $this->get_results($sql, $results_type);

        // Se abbiamo errore ritorno false
        if (!$results) {
            return null;
        }
        // eseguo la query e conto i risultati totali
        if (!$total_count = $this->get_var('SELECT FOUND_ROWS()')) {
            return null;
        }

        // fluscho risultati
        $this->flush();
        // Setto i dati nella classe parent
        $pager->set_pager($total_count);
        // setto la paginazione
        $pager->set_pageList();
        // Prendo paginazione
        $pagination = $pager->get_pagination();
        // Prendo lista pagine presenti
        $pager->get_pageList();
        // Lista pagine
        $page_list = $pager->get_pageList();
        $pager->__destruct();

        // ritorno array risultato paginato
        return array('results'=>s($results),'pagination'=>$pagination,'page_list'=>$page_list,'total_count'=>$total_count);
    }
    /**  
    * @desc Start transaction or commit or rollback
    * @access public
    * @param string $sql Possibili valori: 'START TRANSACTION', 'COMMIT', 'ROLLBACK'
    * @return bool true
    */
    public function transaction($sql='START TRANSACTION') 
    {
        switch($sql) {
            case 'START TRANSACTION':
                $this->haveTransaction = true;
                mysqli_begin_transaction($this->dbh, MYSQLI_TRANS_START_READ_WRITE);
            break;
            case 'SET autocommit=0':
                $this->haveTransaction = true;
                mysqli_autocommit($this->dbh, false);
            break;
            case 'SET autocommit=1':
                $this->haveTransaction = true;
                mysqli_autocommit($this->dbh, true);
            break;
            case 'COMMIT':
                $this->haveTransaction = true;
                mysqli_commit($this->dbh);
            break;
            case 'ROLLBACK':
                $this->haveTransaction = true;
                mysqli_rollback($this->dbh);                
            break;
            default:
                trigger_error('Transaction Flag not valid!!', E_USER_ERROR);
                exit;
        }
        return true;
    }
    /**  
    * @desc Locks delle tabelle nel db.
    * @access public
    * @param array   $locks Tabelle da lockare 
    * @param bool    $mode Modalità del blocco (default tutte le modalità) opzioni WRITE, READ
    * @example
    * <code>
    * 
    * // Blocco la tabella in scrittura
    * if (!$tkdb->lockTable(array('news'=>'WRITE'))) {
    *    // Log error
    * }
    *
    * // Query aggiornamento visite della news 
    * $tkdb->query("UPDATE news SET visite = visite+1 WHERE newsId = '$this->id_dettail'");        
    *
    * // Sblocco la tabella
    * if (!$tkdb->unlockTable()) {          
    *    // Log error
    * }
    * 
    * </code>
    */
    public function lockTable($locks = null, $mode=null) 
    {
        // Get parameter 1.
        if (!isset($locks) && is_array($locks) && count($locks)) {
            return false;
        }
        $tables = array_keys($locks);
        if (!count($tables)) {
            return false;
        } 
        
        // Lock tables.
        $pairs = array();
        foreach ($tables as $table) {
            array_push($pairs, "$table " . $locks[$table]);
        }
        // Query
        $sql = 'LOCK TABLES ' . implode(', ', $pairs);
        if ($mode !== null) {
            $sql .= ' '.$mode;
        }
        if (!$this->query($sql)) {
            trigger_error('SQL ERROR LOOK TABLE IN SQL: '.$sql, E_USER_ERROR);
            exit;
        }
        return true;
    }
    /**
    * @desc sblocca le tabelle bloccate.
    * @access public
    */
    public function unlockTable() 
    {
        // Se non riesco a sbloccare  
        if (!$this->query('UNLOCK TABLES')) {
            $ping = mysqli_ping($this->dbh);
            if ($ping) {
                if (!$this->query('UNLOCK TABLES')) {
                    trigger_error('unlockTable() - Failed to unlock tables, even after reconnect.', E_USER_ERROR);
                    exit;
                }
            }
            else {
                trigger_error('unlockTable() - Failed to unlock tables. Unable to reconnect to the MySQL server.', E_USER_ERROR);
                exit;
            }
        }
        return true;
    }
    /**
    * @desc sblocca tabelle bloccate  o transazioni in corso
    * @access protected
    */
    protected function clearLockTransaction()
    {        
        if ($this->haveTransaction) {
            $this->transaction('ROLLBACK');
        }
        // Eseguo sblocco tabelle per evitare di lasciarne bloccate
        if ($this->haveLook) {
            $this->unlockTable();
        }
    }
}
