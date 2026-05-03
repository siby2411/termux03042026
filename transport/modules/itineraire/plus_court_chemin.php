<?php
// Représentation des noeuds (intersections à Dakar)
function dijkstra($graph, $source, $destination) {
    $dist = [];
    $prev = [];
    $nodes = array_keys($graph);
    
    foreach ($nodes as $node) {
        $dist[$node] = INF;
        $prev[$node] = null;
    }
    $dist[$source] = 0;
    
    $queue = $nodes;
    while (!empty($queue)) {
        usort($queue, function($a, $b) use ($dist) {
            return $dist[$a] - $dist[$b];
        });
        $u = array_shift($queue);
        
        if ($u == $destination) break;
        
        foreach ($graph[$u] as $neighbor => $weight) {
            $alt = $dist[$u] + $weight;
            if ($alt < $dist[$neighbor]) {
                $dist[$neighbor] = $alt;
                $prev[$neighbor] = $u;
            }
        }
    }
    
    // Reconstruction du chemin
    $path = [];
    $u = $destination;
    while ($prev[$u] !== null) {
        array_unshift($path, $u);
        $u = $prev[$u];
    }
    array_unshift($path, $source);
    
    return ['distance' => $dist[$destination], 'path' => $path];
}

// Exemple : graphe des quartiers de Dakar (distances en km)
$graph = [
    'Plateau' => ['Fann' => 2.5, 'Mermoz' => 4.2, 'Gueule_Tapee' => 1.8],
    'Fann' => ['Plateau' => 2.5, 'Ouakam' => 3.0, 'Mermoz' => 2.2],
    'Mermoz' => ['Plateau' => 4.2, 'Fann' => 2.2, 'Sacre_Coeur' => 2.8, 'Almadies' => 5.0],
    'Ouakam' => ['Fann' => 3.0, 'Almadies' => 2.5],
    'Almadies' => ['Ouakam' => 2.5, 'Mermoz' => 5.0, 'Ngor' => 3.0],
    'Gueule_Tapee' => ['Plateau' => 1.8, 'Colobane' => 2.0],
    'Colobane' => ['Gueule_Tapee' => 2.0, 'Grand_Yoff' => 3.5],
    'Grand_Yoff' => ['Colobane' => 3.5, 'Sacre_Coeur' => 2.0],
    'Sacre_Coeur' => ['Mermoz' => 2.8, 'Grand_Yoff' => 2.0],
    'Ngor' => ['Almadies' => 3.0]
];

// Exemple : trouver chemin entre Sacre_Coeur et Plateau
$result = dijkstra($graph, 'Sacre_Coeur', 'Plateau');
echo "Distance : " . $result['distance'] . " km\n";
echo "Chemin : " . implode(" → ", $result['path']);
?>
