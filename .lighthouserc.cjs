module.exports = {
    ci: {
        collect: {
            url: ['http://127.0.0.1:3000/'],
            numberOfRuns: 1,
            startServerCommand: 'php artisan serve --host=127.0.0.1 --port=3000',
            startServerReadyPattern: 'Development Server started',
        },
        assert: {
            assertions: {
                'categories:accessibility': ['error', { minScore: 0.9 }],
                'categories:best-practices': ['error', { minScore: 0.9 }],
                'categories:seo': ['warn', { minScore: 0.8 }],
                'categories:performance': ['warn', { minScore: 0.65 }],
            },
        },
        upload: {
            target: 'temporary-public-storage',
        },
    },
};
