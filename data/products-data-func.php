<?php
    $config = include_once 'config.php';
    $conn = new mysqli($config['host'], $config['name'], $config['pass'], $config['db']);
    if(!$conn) exit;

    $columnsCount = count($qColumns);

    $page = $conn->real_escape_string($_REQUEST['page'] ?? 0);
    $limit = $conn->real_escape_string($_REQUEST['limit'] ?? 20);
    $orderby = $conn->real_escape_string($_REQUEST['orderby'] ?? 0);
    $order_direction = $conn->real_escape_string($_REQUEST['order_direction'] ?? 'ASC');
    $search_text = $conn->real_escape_string($_REQUEST['search_text'] ?? '');
    $offset = $limit * $page;

    $qLimit = "LIMIT $offset, $limit;";
    $qSelect = implode(', ', $qColumns);
    if(strlen($search_text) > 0) {
        $qWhere .= (strpos($qWhere, 'WHERE') === false ? "WHERE (" : " AND (");
        $first = true;
        foreach($qColumns as $column) {
            $qWhere .= ($first ? "" : " OR ") . explode(' as ', $column)[0] . " LIKE '%$search_text%'";
            $first = false;
        }
        $qWhere .= ")";
    }
    $qCountRows = ", (SELECT COUNT(*) FROM $qFrom $qWhere) as total_rows";
    $qOrderBy = "ORDER BY {$qColumns[$orderby]} $order_direction";

    $result = $conn->query("SELECT $qSelect $qCountRows FROM $qFrom $qJoin $qWhere $qOrderBy $qLimit");

    $rData = $result->fetch_all(MYSQLI_BOTH);

    $output = array();
    for($a = 0; $a < count($rData); $a++) {
        $row = array();
        for($i = 0; $i < $columnsCount; $i++)
            array_push($row, $rData[$a][$i]);
        array_push($output, $row);
    }

    echo json_encode(array([
        'data' => $output,
        'total_rows' => ($rData[0]['total_rows'] ?? 0)
    ]));
    exit;