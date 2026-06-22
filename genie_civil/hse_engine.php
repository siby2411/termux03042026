<?php

if (!class_exists("OmegaAI")) {

class OmegaAI {

    public static function riskLevel($budget, $spent, $delayDays) {

        $ratio = ($budget > 0) ? ($spent / $budget) * 100 : 0;

        if ($ratio > 110 || $delayDays > 30) {
            return ["level"=>"CRITICAL","color"=>"red","score"=>$ratio];
        }

        if ($ratio > 100 || $delayDays > 15) {
            return ["level"=>"HIGH","color"=>"orange","score"=>$ratio];
        }

        return ["level"=>"NORMAL","color"=>"green","score"=>$ratio];
    }

    public static function fraudDetection($prevu, $reel) {
        if ($reel > $prevu * 1.25) {
            return "FRAUD_ALERT";
        }
        return "OK";
    }

    public static function productivity($heures, $paiement) {
        if ($heures <= 0) return 0;
        return round($paiement / $heures, 2);
    }

    public static function delayPredictor($progress, $daysElapsed) {
        if ($progress <= 0) return 999;
        return round(($daysElapsed / max($progress,1)) * 100);
    }
}

}
?>
