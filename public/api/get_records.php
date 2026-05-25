<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db_connect.php';

try {
    // Optional filters
    $provinceId = isset($_GET['province']) ? $_GET['province'] : (isset($_POST['province']) ? $_POST['province'] : null);
    $municipalityId = isset($_GET['municipality']) ? $_GET['municipality'] : (isset($_POST['municipality']) ? $_POST['municipality'] : null);
    $semester = isset($_GET['semester']) ? $_GET['semester'] : (isset($_POST['semester']) ? $_POST['semester'] : null);
    $year = isset($_GET['year']) ? $_GET['year'] : (isset($_POST['year']) ? $_POST['year'] : null);
    $employeeId = isset($_GET['employee_id']) ? $_GET['employee_id'] : (isset($_POST['employee_id']) ? $_POST['employee_id'] : null);

    $sql = "
        SELECT 
            'record' as type,
            r.id,
            CONCAT(e.first_name, ' ', e.last_name) as employee_name,
            e.employee_id,
            r.role,
            prov.name as province_name,
            prov.id as province_id,
            mun.name as municipality_name,
            mun.id as municipality_id,
            sch.name as school_name,
            sch.id as school_id,
            r.semester,
            r.school_year,
            r.file_path,
            r.status,
            r.uploaded_at,
            r.created_at
        FROM ipcrf_records r
        JOIN employees e ON r.employee_id = e.id
        JOIN schools sch ON e.school_id = sch.id
        JOIN municipalities mun ON sch.municipality_id = mun.id
        JOIN provinces prov ON mun.province_id = prov.id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($provinceId)) {
        $sql .= " AND prov.id = :province_id";
        $params[':province_id'] = $provinceId;
    }
    if (!empty($municipalityId)) {
        $sql .= " AND mun.id = :municipality_id";
        $params[':municipality_id'] = $municipalityId;
    }
    if (!empty($semester)) {
        $sql .= " AND r.semester = :semester";
        $params[':semester'] = $semester;
    }
    if (!empty($year)) {
        $sql .= " AND r.school_year = :year";
        $params[':year'] = $year;
    }
    if (!empty($employeeId)) {
        $sql .= " AND (e.employee_id LIKE :employee_id OR e.first_name LIKE :employee_id OR e.last_name LIKE :employee_id)";
        $params[':employee_id'] = '%' . $employeeId . '%';
    }

    $sql .= " UNION ALL SELECT 
            'wizard' as type,
            w.id,
            w.name as employee_name,
            'N/A' as employee_id,
            'N/A' as role,
            w.province as province_name,
            (SELECT id FROM provinces WHERE name = w.province LIMIT 1) as province_id,
            w.municipality as municipality_name,
            (SELECT id FROM municipalities WHERE name = w.municipality LIMIT 1) as municipality_id,
            'N/A' as school_name,
            NULL as school_id,
            'N/A' as semester,
            YEAR(w.created_at) as school_year,
            w.scanned_file_path as file_path,
            w.status,
            w.created_at as uploaded_at,
            w.created_at
        FROM ipcrfs w
        WHERE 1=1
    ";

    if (!empty($provinceId)) {
        $sql .= " AND w.province = (SELECT name FROM provinces WHERE id = :province_id2 LIMIT 1)";
        $params[':province_id2'] = $provinceId;
    }
    if (!empty($municipalityId)) {
        $sql .= " AND w.municipality = (SELECT name FROM municipalities WHERE id = :municipality_id2 LIMIT 1)";
        $params[':municipality_id2'] = $municipalityId;
    }
    if (!empty($semester)) {
        // Wizard uploads do not have semesters, force empty set if filtering by semester
        $sql .= " AND 1=0";
    }
    if (!empty($year)) {
        $sql .= " AND YEAR(w.created_at) = :year2";
        $params[':year2'] = $year;
    }
    if (!empty($employeeId)) {
        $sql .= " AND w.name LIKE :employee_id2";
        $params[':employee_id2'] = '%' . $employeeId . '%';
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $records
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
