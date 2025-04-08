@if(isset($theme))
<style>
    :root {
        --primary-color: {{ $theme['primary_color'] }};
        --secondary-color: {{ $theme['secondary_color'] }};
        --accent-color: {{ $theme['accent_color'] }};
        --text-color: {{ $theme['text_color'] }};
        --background-color: {{ $theme['background_color'] }};
    }

    /* Only apply theme styles to content area, not the sidebar or header */
    .content-wrapper, .main-content {
        color: var(--text-color);
        background-color: var(--background-color);
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-primary:hover {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    .btn-secondary {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    .btn-secondary:hover {
        background-color: var(--accent-color);
        border-color: var(--accent-color);
    }

    .text-primary {
        color: var(--primary-color) !important;
    }

    .text-secondary {
        color: var(--secondary-color) !important;
    }

    .text-accent {
        color: var(--accent-color) !important;
    }

    .bg-primary {
        background-color: var(--primary-color) !important;
    }

    .bg-secondary {
        background-color: var(--secondary-color) !important;
    }

    .bg-accent {
        background-color: var(--accent-color) !important;
    }

    .border-primary {
        border-color: var(--primary-color) !important;
    }

    .border-secondary {
        border-color: var(--secondary-color) !important;
    }

    .border-accent {
        border-color: var(--accent-color) !important;
    }

    .card {
        background-color: var(--secondary-color);
    }

    .navbar {
        background-color: var(--primary-color);
    }

    .nav-link {
        color: var(--text-color);
    }

    .nav-link:hover {
        color: var(--accent-color);
    }
</style>
@endif 