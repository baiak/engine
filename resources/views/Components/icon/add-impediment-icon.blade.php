<svg {{ $attributes->merge(['class' => 'h-4 w-4']) }} viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" fill="none">
    <!-- Triângulo amarelo -->
    <path d="M32 8 L56 52 H8 Z" fill="#FFD700" stroke="#E0B000" stroke-width="2" />

    <!-- Exclamação simplificada -->
    <rect x="30" y="22" width="4" height="14" fill="#000" rx="1" />
    <circle cx="32" cy="40" r="2" fill="#000" />

    <!-- Círculo azul com + bem maior -->
    <g transform="translate(38, 38)">
        <!-- Círculo de 13px de raio (diâmetro 26px ≈ 20% menor que 32) -->
        <circle cx="13" cy="13" r="13" fill="#007BFF" />
        <!-- Barra vertical do "+" -->
        <rect x="12" y="5" width="2" height="17" fill="white" />
        <!-- Barra horizontal do "+" -->
        <rect x="5" y="12" width="17" height="2" fill="white" />
    </g>
</svg>
