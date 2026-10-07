(function () {
    'use strict';

    function trim(value) {
        return value.replace(/^\s+|\s+$/g, '');
    }

    function initProjectName() {
        var title = document.getElementById('downloads-filetitle');
        var project = document.getElementById('downloads-project');

        if (!title || !project) {
            return;
        }

        var autoProject = trim(project.value) === '';

        function derivedProject() {
            return trim(title.value);
        }

        if (autoProject && derivedProject() !== '') {
            project.value = derivedProject();
        }

        title.addEventListener('input', function () {
            if (autoProject) {
                project.value = derivedProject();
            }
        });

        project.addEventListener('input', function () {
            autoProject = trim(project.value) === '' || trim(project.value) === derivedProject();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProjectName);
    } else {
        initProjectName();
    }
}());
