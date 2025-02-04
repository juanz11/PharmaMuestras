<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#0d6efd] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0b5ed7] focus:bg-[#0b5ed7] active:bg-[#0a58ca] focus:outline-none focus:ring-2 focus:ring-[#0d6efd] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

<style>
.bg-\[\#0d6efd\] {
    background-color: #0d6efd !important;
}
.hover\:bg-\[\#0b5ed7\]:hover {
    background-color: #0b5ed7 !important;
}
.focus\:bg-\[\#0b5ed7\]:focus {
    background-color: #0b5ed7 !important;
}
.active\:bg-\[\#0a58ca\]:active {
    background-color: #0a58ca !important;
}
.focus\:ring-\[\#0d6efd\]:focus {
    --tw-ring-color: #0d6efd !important;
}
</style>
