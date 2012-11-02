<?php

header("Cache-Control: no-cache, must-revalidate");

define('BN_CONFIG_FILE', 'Conf/Conf.ini');

$config = parse_ini_file(BN_CONFIG_FILE, true);
$prefix = $config['database']['prefix'];

class SPDO {
    /**
     * Instance de la classe PDO
     *
     * @var PDO
     * @access private
     */
    private $PDOInstance = null;

    /**
     * Instance de la classe SPDO
     *
     * @var SPDO
     * @access private
     * @static
     */
    private static $instance = null;

    /**
     * Constructeur
     *
     * @param void
     * @return void
     * @see PDO::__construct()
     * @access private
     */
    private function __construct()
    {
        $config = parse_ini_file(BN_CONFIG_FILE, true);

        $host = $config['database']['host'];
        $user = $config['database']['user'];
        $pwd = $config['database']['pwd'];
        $base = $config['database']['base'];
        $prefix = $config['database']['prefix'];

        $this->PDOInstance = new PDO('mysql:dbname='.$base.';host='.$host, $user ,$pwd);
        $this->PDOInstance->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Crée et retourne l'objet SPDO
     *
     * @access public
     * @static
     * @param void
     * @return SPDO $instance
     */
    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new SPDO();
        }
        return self::$instance;
    }

    /**
     * Exécute une requête SQL avec PDO
     *
     * @param string $query La requête SQL
     * @return PDOStatement Retourne l'objet PDOStatement
     */
    public static function query($query)
    {
        return self::getInstance()->PDOInstance->query($query);
    }

    /**
     * Prepare une requête SQL avec PDO
     *
     * @param string $query La requête SQL
     * @return PDOStatement Retourne l'objet PDOStatement
     */
    public static function prepare($query)
    {
        return self::getInstance()->PDOInstance->prepare($query);
    }
}

try
{
    $sql = "SELECT mtch_num, mtch_begin, mtch_court
			FROM ${prefix}matchs
			WHERE mtch_court <> 0
				AND mtch_score = ''
			ORDER BY mtch_begin asc";

    $select = SPDO::query($sql);

    foreach ($select as $row) {
		$begin = strtotime($row['mtch_begin']);
		$result[$row['mtch_num']] = array("court" => $row['mtch_court'], "begin" => $begin);
	}

	print(json_encode($result));

    $select->closeCursor();
}
catch(PDOException $e)
{
    print('Erreur : '.$e->getMessage());
}
