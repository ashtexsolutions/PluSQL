<?php
    require_once('table_inspector.class.php');
    require_once('table_inspector_worker.class.php');
    $link = new mysqli(DBHOST,DBUSER,DBPASS);
    $link->select_db('plusql');
    $worker = TableInspector::forTable('author',$link);
    print_r($worker->primaryKeys());
    $worker = TableInspector::forTable('book',$link);
    print_r($worker->primaryKeys());
    $worker = TableInspector::forTable('reader_reviews_book',$link);
    print_r($worker->primaryKeys());
    $worker = TableInspector::forTable('reader_reviews_book',$link);
    print_r($worker->primaryKeys());
