<?php

/**
 * Validation Script for Bobot Penilaian Category Refactoring
 * 
 * This script validates that the refactoring from degree levels to categories
 * has been completed successfully.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== BOBOT PENILAIAN CATEGORY REFACTORING VALIDATION ===\n\n";

$passed = 0;
$failed = 0;

// Test 1: Study Program Categories exist
echo "Test 1: Checking StudyProgramCategory table...\n";
$categoryCount = App\Models\StudyProgramCategory::count();
if ($categoryCount === 6) {
    echo "  ✓ PASS: Found 6 categories\n";
    $passed++;
} else {
    echo "  ✗ FAIL: Expected 6 categories, found {$categoryCount}\n";
    $failed++;
}

// Test 2: All categories have correct codes
echo "\nTest 2: Validating category codes...\n";
$expectedCodes = ['AK', 'MAG', 'DOK', 'VOK', 'MT', 'PRO'];
$actualCodes = App\Models\StudyProgramCategory::pluck('code')->toArray();
sort($expectedCodes);
sort($actualCodes);
if ($expectedCodes === $actualCodes) {
    echo "  ✓ PASS: All category codes are correct\n";
    $passed++;
} else {
    echo "  ✗ FAIL: Category codes mismatch\n";
    echo "    Expected: " . implode(', ', $expectedCodes) . "\n";
    echo "    Got: " . implode(', ', $actualCodes) . "\n";
    $failed++;
}

// Test 3: Bobot Penilaian records exist
echo "\nTest 3: Checking BobotPenilaian records...\n";
$bobotCount = App\Models\BobotPenilaian::count();
$expectedBobot = 30 * 6; // 30 elements × 6 categories
if ($bobotCount === $expectedBobot) {
    echo "  ✓ PASS: Found {$expectedBobot} bobot records\n";
    $passed++;
} else {
    echo "  ✗ FAIL: Expected {$expectedBobot} records, found {$bobotCount}\n";
    $failed++;
}

// Test 4: BobotPenilaian has id_category column
echo "\nTest 4: Validating BobotPenilaian structure...\n";
try {
    $sampleBobot = App\Models\BobotPenilaian::first();
    if ($sampleBobot && isset($sampleBobot->id_category)) {
        echo "  ✓ PASS: id_category column exists\n";
        $passed++;
    } else {
        echo "  ✗ FAIL: id_category column not found\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "  ✗ FAIL: Error checking structure: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 5: BobotPenilaian category relationship works
echo "\nTest 5: Testing category relationship...\n";
try {
    $bobot = App\Models\BobotPenilaian::with('category')->first();
    if ($bobot && $bobot->category && $bobot->category->code) {
        echo "  ✓ PASS: Category relationship works (sample: {$bobot->category->code})\n";
        $passed++;
    } else {
        echo "  ✗ FAIL: Category relationship not working\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "  ✗ FAIL: Error testing relationship: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 6: No id_level references in BobotPenilaian
echo "\nTest 6: Checking for old id_level column...\n";
try {
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('bobot_penilaian');
    if (!in_array('id_level', $columns)) {
        echo "  ✓ PASS: id_level column removed\n";
        $passed++;
    } else {
        echo "  ✗ FAIL: id_level column still exists\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "  ✗ FAIL: Error checking columns: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 7: Unique constraint on (id_elemen, id_category)
echo "\nTest 7: Validating unique constraint...\n";
try {
    $firstBobot = App\Models\BobotPenilaian::first();
    if ($firstBobot) {
        try {
            App\Models\BobotPenilaian::create([
                'id_elemen' => $firstBobot->id_elemen,
                'id_category' => $firstBobot->id_category,
                'bobot' => 50
            ]);
            echo "  ✗ FAIL: Unique constraint not working (duplicate allowed)\n";
            $failed++;
        } catch (\Illuminate\Database\QueryException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false || strpos($e->getMessage(), 'UNIQUE') !== false) {
                echo "  ✓ PASS: Unique constraint enforced\n";
                $passed++;
            } else {
                echo "  ✗ FAIL: Unexpected error: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
    } else {
        echo "  ⚠ SKIP: No bobot records to test\n";
    }
} catch (Exception $e) {
    echo "  ✗ FAIL: Error testing constraint: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 8: Each category has bobots for all elements
echo "\nTest 8: Checking bobot distribution...\n";
$elemenCount = App\Models\ElemenStandar::count();
$allGood = true;
foreach (App\Models\StudyProgramCategory::all() as $category) {
    $bobotForCategory = App\Models\BobotPenilaian::where('id_category', $category->id)->count();
    if ($bobotForCategory !== $elemenCount) {
        echo "  ✗ Category {$category->code}: {$bobotForCategory}/{$elemenCount} elements\n";
        $allGood = false;
    }
}
if ($allGood) {
    echo "  ✓ PASS: All categories have bobot for all elements\n";
    $passed++;
} else {
    echo "  ✗ FAIL: Uneven distribution\n";
    $failed++;
}

// Summary
echo "\n=== VALIDATION SUMMARY ===\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "Total:  " . ($passed + $failed) . "\n\n";

if ($failed === 0) {
    echo "✓ ALL TESTS PASSED - Refactoring completed successfully!\n";
    exit(0);
} else {
    echo "✗ SOME TESTS FAILED - Please review the errors above.\n";
    exit(1);
}
