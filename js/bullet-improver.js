
document.addEventListener("DOMContentLoaded", function () {

    const improveBtn = document.getElementById("improveBtn");
    const bulletInput = document.getElementById("bulletInput");
    const improvedSection = document.getElementById("improvedSection");
    const originalText = document.getElementById("originalText");
    const improvedText = document.getElementById("improvedText");
    const copyImprovedBtn = document.getElementById("copyImprovedBtn");

    if (!improveBtn || !bulletInput) return;

    function improveBullet(line) {
        let text = line.trim().replace(/[.!?]+$/, "");
        const lower = text.toLowerCase();

        if (lower.includes("student management website")) {
            return "Developed a student management web application to organize and manage student information efficiently.";
        }

        if (lower.includes("frontend design") || lower.includes("html and css")) {
            return "Designed and developed user-friendly frontend interfaces using HTML and CSS.";
        }

        if (lower.includes("fixed errors") || lower.includes("fixed bugs")) {
            return "Identified, debugged, and resolved website issues to improve functionality and user experience.";
        }

        if (lower.includes("database management") || lower.includes("mysql")) {
            return "Implemented database operations using MySQL to store, manage, and retrieve application data.";
        }

        if (/^worked on/i.test(text)) {
            return "Contributed to the development of " + text.replace(/^worked on\s*/i, "") + " to support project objectives.";
        }

        if (/^helped/i.test(text)) {
            return "Supported project development by " + text.replace(/^helped\s*(with)?\s*/i, "") + ".";
        }

        if (/^created/i.test(text)) {
            return "Designed and developed " + text.replace(/^created\s*/i, "") + " with a focus on usability and functionality.";
        }

        if (/^learned/i.test(text)) {
            return "Gained practical knowledge of " + text.replace(/^learned\s*/i, "") + " through hands-on project experience.";
        }

        if (/^used/i.test(text)) {
            return "Applied " + text.replace(/^used\s*/i, "") + " to implement project features and functionality.";
        }

        if (/^made/i.test(text)) {
            return "Developed " + text.replace(/^made\s*/i, "") + " to improve project functionality.";
        }

        return "Contributed to " + text.charAt(0).toLowerCase() + text.slice(1) + " through practical project work.";
    }

    improveBtn.addEventListener("click", function () {
        const text = bulletInput.value.trim();

        if (!text) {
            alert("Please enter your resume bullet points first.");
            bulletInput.focus();
            return;
        }

        const improved = text
            .split(/\n+/)
            .filter(line => line.trim())
            .map(improveBullet)
            .join("\n");

        if (originalText) originalText.textContent = text;
        if (improvedText) improvedText.textContent = improved;

        if (improvedSection) {
            improvedSection.hidden = false;
            improvedSection.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });
        }
    });

    if (copyImprovedBtn) {
        copyImprovedBtn.addEventListener("click", async function () {
            const text = improvedText?.textContent || "";

            try {
                await navigator.clipboard.writeText(text);
                alert("Improved bullet points copied!");
            } catch (error) {
                alert("Copy failed. Please copy manually.");
            }
        });
    }
});
