<?php
    namespace plusql;
    use Plusql;

    /**
    * Start off by testing building the from clause
    */
    \murphy\Test::add(function($runner)
    {
        $conn = getConnection();
        $sel = new Select($conn);
        $sel->strong_guy;
        
        if($sel->buildFromClause() == 'from strong_guy')
            $runner->pass();
        else
            $runner->fail('Unable to build a single table strong clause for strong_guy');
        
        $sel->weak_guy;
        
        if($sel->buildFromClause() == 'from strong_guy INNER JOIN weak_guy ON strong_guy.strong_guy_id = weak_guy.strong_guy_id')
            $runner->pass();
        else
            $runner->fail('Unable to append a weak_guy to the from clause for strong_guy');
        
        $sel->french_guy('weak_guy')->rogue_guy;
        
        if($sel->buildFromClause() == 'from strong_guy INNER JOIN weak_guy ON strong_guy.strong_guy_id = weak_guy.strong_guy_id INNER JOIN french_guy ON weak_guy.french_guy_id = french_guy.french_guy_id INNER JOIN is_rogue ON weak_guy.strong_guy_id = is_rogue.strong_guy_id AND weak_guy.weak_guy_id = is_rogue.weak_guy_id INNER JOIN rogue_guy ON is_rogue.rogue_guy_id = rogue_guy.rogue_guy_id')
            $runner->pass();
        else
            $runner->fail('Unable to join french and rogue guys to the weak guy, using __call() and weak_guy as the target');
        
        $sel = new Select($conn);
        $sel->strong_guy->weak_guy->french_guy->rogue_guy;
        
        try
        {
            $sel->buildFromClause();
            $sel->fail('Why were we able to call strong_guy->weak_guy->french_guy->rogue_guy? There should be no way to join french_guy to rogue_guy');
        }
        
        catch(UnableToDetermineOnClauseException $exc)
        {
            $runner->pass();
        }
    });

    /**
    * Now test the ability to build each of the clauses:
    * select
    * where
    * group by
    * order by
    * having
    * limit 
    */
    \murphy\Test::add(function($runner)
    {
        $conn = getConnection();
        $sel = new Select($conn);
        $properties = array('select' => array('strong_name',',weak_name'),
                            'where' => array('strong_name = \'Strongy Strongo\'',' AND weak_name = \'Weak Guy 1\''),
                            'groupBy' => array('strong_guy_id',',weak_guy_id'),
                            'having' => array('strong_guy_id > 4',' AND weak_guy_id = 7'),
                            'orderBy' => array('strong_guy_id ASC',',weak_guy_id DESC'),
                            'limit' => array('100',NULL),
                           );
        foreach($properties as $name => $data)
            testProperty($conn,$runner,$name,$data[0],$data[1]);
    });
    
    /**
    * Now just do a bit of an end-to-end test building a big fuckoff query
    */
    \murphy\Test::add(function($runner)
    {
        $sel = new Select(getConnection());
        $query = $sel->strong_guy
                     ->weak_guy
                     ->rogue_guy('weak_guy')
                     ->french_guy
                     ->select('strong_guy.strong_name,weak_guy.weak_name,rogue_guy.rogue_name,french_guy.french_name')
                     ->where('strong_guy.strong_guy_id > 1')
                     ->groupBy('strong_guy.strong_guy_id')
                     ->having('weak_guy_id > 1')
                     ->orderBy('strong_guy.strong_guy_id,weak_guy.weak_guy_id')
                     ->limit('100')
                     ->sql();
        
        if($query == 'SELECT strong_guy.strong_name,weak_guy.weak_name,rogue_guy.rogue_name,french_guy.french_name from strong_guy INNER JOIN weak_guy ON strong_guy.strong_guy_id = weak_guy.strong_guy_id INNER JOIN is_rogue ON weak_guy.strong_guy_id = is_rogue.strong_guy_id AND weak_guy.weak_guy_id = is_rogue.weak_guy_id INNER JOIN rogue_guy ON is_rogue.rogue_guy_id = rogue_guy.rogue_guy_id INNER JOIN french_guy ON weak_guy.french_guy_id = french_guy.french_guy_id WHERE strong_guy.strong_guy_id > 1 GROUP BY strong_guy.strong_guy_id HAVING weak_guy_id > 1 ORDER BY strong_guy.strong_guy_id,weak_guy.weak_guy_id LIMIT 100')
            $runner->pass();
        else
            $runner->fail('Unexpected output from big fuckoff query using Select');
    });

    function getConnection()
    {
        $conn = NULL;
        \murphy\Fixture::load(dirname(__FILE__).'/../on_clause.class.php.murphy/fixture.php')
        ->also(dirname(__FILE__).'/../query_iterator.class.php.murphy/fixture.php')
        ->execute(function($aliases) use(&$conn)
        {
            $aliases = $aliases['plusql'];
            $host = $aliases[0];
            $username = $aliases[1];
            $password = $aliases[2];
            $dbname = $aliases[3];
            $conn = new Connection($host,$username,$password,$dbname);
            $conn->connect();
        });
        
        return $conn;
    }
    
    function testProperty(Connection $conn,\murphy\Test $runner,$name,$initial,$additional)
    {
        $sel = new Select($conn);
        $sel->$name($initial);
        
        if($sel->$name() == $initial)
            $runner->pass();
        else
            $runner->fail('Unable to set '.$name.' clause to '.$initial);
        
        if($additional)
        {
            $sel->$name($sel->$name().$additional);
            
            if($sel->$name() == $initial.$additional)
                $runner->pass();
            else
                $runner->fail('Unable to update '.$name.' clause to '.$initial.$additional);
        }
    }
