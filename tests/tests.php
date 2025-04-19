<?php

require_once __DIR__ . '/testframework.php';
$config = require __DIR__ . '/../site/config.php';
require_once __DIR__ . '/../site/modules/database.php';
require_once __DIR__ . '/../site/modules/page.php';

$tf = new TestFramework();

// Test 1: conexiune DB
$tf->add('DbConnection', function() use ($config) {
    try {
        $db = new Database($config['db']['path']);
        return assertExpression($db instanceof Database, 'DB OK', 'DB FAIL');
    } catch (Exception $e) {
        return assertExpression(false, '', 'Exception la conexiune: ' . $e->getMessage());
    }
});

// Test 2: Count
$tf->add('CountMethod', function() use ($config) {
    $db = new Database($config['db']['path']);
    $count = $db->Count('page');
    return assertExpression($count === 3, "Count=3", "Count!=3 (got $count)");
});

// Test 3: Create
$tf->add('CreateMethod', function() use ($config) {
    $db = new Database($config['db']['path']);
    $newId = $db->Create('page', ['title'=>'T','content'=>'C']);
    return assertExpression(is_numeric($newId), "Create OK (id=$newId)", "Create FAIL");
});

// Test 4: Read
$tf->add('ReadMethod', function() use ($config) {
    $db = new Database($config['db']['path']);
    $row = $db->Read('page', 1);
    return assertExpression(isset($row['id']) && $row['id']==1, 'Read OK','Read FAIL');
});

// Test 5: Update
$tf->add('UpdateMethod', function() use ($config) {
    $db = new Database($config['db']['path']);
    $db->Update('page', 1, ['title'=>'X','content'=>'Y']);
    $row = $db->Read('page',1);
    return assertExpression($row['title']==='X' && $row['content']==='Y','Update OK','Update FAIL');
});

// Test 6: Delete
$tf->add('DeleteMethod', function() use ($config) {
    $db = new Database($config['db']['path']);
    $db->Create('page',['title'=>'T','content'=>'C']);
    $id = $db->Count('page');
    $res = $db->Delete('page', $id);
    return assertExpression($res,'Delete OK','Delete FAIL');
});

// Test 7: Page::Render
$tf->add('PageRender', function() {
    $tpl = sys_get_temp_dir() . '/test.tpl';
    file_put_contents($tpl, 'Hello {{name}}');
    $p = new Page($tpl);
    $out = $p->Render(['name'=>'World']);
    return assertExpression($out==='Hello World','Render OK','Render FAIL');
});

$tf->run();
echo $tf->getResult() . PHP_EOL;
