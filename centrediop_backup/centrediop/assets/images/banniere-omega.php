<?php
header('Content-Type: image/svg+xml');
echo <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="120" viewBox="0 0 800 120">
    <defs>
        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#0056b3" />
            <stop offset="100%" stop-color="#0078d7" />
        </linearGradient>
    </defs>
    <rect width="800" height="120" fill="url(#gradient)" />
    <text x="400" y="40" font-family="Arial, sans-serif" font-size="24" font-weight="bold" text-anchor="middle" fill="white">Omega Informatique CONSULTING</text>
    <text x="400" y="70" font-family="Arial, sans-serif" font-size="18" text-anchor="middle" fill="white">Centre de Santé Mamadou Diop - Dakar, Sénégal</text>
    <text x="400" y="100" font-family="Arial, sans-serif" font-size="14" text-anchor="middle" fill="white">Gestion Intégrée des Soins de Santé</text>
</svg>
SVG;
