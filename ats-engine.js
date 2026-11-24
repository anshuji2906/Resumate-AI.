/* =========================================================
   RESUMATE AI — Explainable ATS Matching Engine
   Rule-based estimate; not an employer ATS score.
========================================================= */
document.addEventListener("DOMContentLoaded", () => {
    const resumeInput = document.getElementById("resumeText");
    const jobInput = document.getElementById("jobDescription");
    const resumeForm = document.getElementById("resumeForm");
    const resultsSection = document.getElementById("resultsSection");
    if (!resumeInput || !jobInput || !resumeForm) {
        console.error("Resumate AI: analyzer form elements are missing.");
        return;
    }

    const skillDictionary = {
        "HTML": ["html", "html5"],
        "CSS": ["css", "css3"],
        "JavaScript": ["javascript", "ecmascript"],
        "React": ["react", "react.js", "reactjs"],
        "Next.js": ["next.js", "nextjs"],
        "Angular": ["angular"],
        "Vue.js": ["vue.js", "vuejs"],
        "TypeScript": ["typescript"],
        "PHP": ["php"],
        "Laravel": ["laravel"],
        "MySQL": ["mysql"],
        "SQL": ["sql", "structured query language"],
        "PostgreSQL": ["postgresql", "postgres"],
        "MongoDB": ["mongodb"],
        "Python": ["python"],
        "Java": ["java"],
        "C++": ["c++"],
        "C#": ["c#"],
        "C Programming": ["c programming", "language c"],
        "Node.js": ["node.js", "nodejs"],
        "Express.js": ["express.js", "expressjs"],
        "REST API": ["rest api", "restful api", "restful services"],
        "GraphQL": ["graphql"],
        "Git": ["git"],
        "GitHub": ["github"],
        "Bootstrap": ["bootstrap"],
        "Tailwind CSS": ["tailwind css", "tailwind"],
        "Firebase": ["firebase"],
        "AWS": ["aws", "amazon web services"],
        "Azure": ["microsoft azure", "azure"],
        "Docker": ["docker"],
        "Kubernetes": ["kubernetes"],
        "Linux": ["linux"],
        "Figma": ["figma"],
        "UI/UX": ["ui/ux", "user interface", "user experience"],
        "Problem Solving": ["problem solving", "problem-solving"],
        "Communication": ["communication", "communication skills"],
        "Teamwork": ["teamwork", "team work", "collaboration"],
        "Data Structures": ["data structures", "data structure"],
        "Algorithms": ["algorithms", "algorithm design"],
        "OOP": ["oop", "object oriented programming", "object-oriented programming"],
        "Machine Learning": ["machine learning"],
        "Artificial Intelligence": ["artificial intelligence"],
        "Deep Learning": ["deep learning"],
        "Statistics": ["statistics", "statistical analysis"],
        "Data Analysis": ["data analysis", "data analytics"],
        "Data Cleaning": ["data cleaning", "data cleansing"],
        "Data Visualization": ["data visualization", "data visualisation"],
        "Excel": ["excel", "microsoft excel"],
        "Power BI": ["power bi"],
        "Tableau": ["tableau"],
        "WordPress": ["wordpress"],
        "SEO": ["seo", "search engine optimization"],
        "Agile": ["agile", "agile methodology"],
        "Scrum": ["scrum"],
        "Jira": ["jira"],
        "Selenium": ["selenium"],
        "Software Testing": ["software testing", "testing"],
        "Cybersecurity": ["cybersecurity", "information security"],
        "Networking": ["computer networking", "networking"],
        "Redis": ["redis"],
        "Django": ["django"],
        "Flask": ["flask"],
        "Spring Boot": ["spring boot"]
    };

    const weakPhrases = {
        "worked on": "Developed / Contributed to",
        "helped with": "Supported / Collaborated on",
        "helped": "Supported / Collaborated",
        "responsible for": "Managed / Owned",
        "participated in": "Contributed to",
        "assisted with": "Supported / Contributed to",
        "involved in": "Contributed to",
        "used": "Implemented / Applied",
        "made": "Designed / Created",
        "did": "Executed / Delivered"
    };

    const preferredHeading = /\b(preferred|nice to have|nice-to-have|optional|desirable|bonus skills)\b/i;
    const requiredHeading = /\b(required|must have|must-have|minimum qualifications|required qualifications|mandatory)\b/i;

    function containsPhrase(text, phrase) {
        const escaped = phrase.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
        return new RegExp("(^|[^a-z0-9])" + escaped + "($|[^a-z0-9])", "i").test(text);
    }

    function extractSkills(text) {
        const lower = text.toLowerCase();
        return Object.entries(skillDictionary)
            .filter(([, aliases]) => aliases.some(alias => containsPhrase(lower, alias.toLowerCase())))
            .map(([skill]) => skill);
    }

    function classifyJobSkills(jobText, jobSkills) {
        const lines = jobText.split(/\r?\n/);
        let preferredMode = false;
        const preferred = new Set();
        const required = new Set();

        lines.forEach(line => {
            if (requiredHeading.test(line)) preferredMode = false;
            if (preferredHeading.test(line)) preferredMode = true;

            jobSkills.forEach(skill => {
                const aliases = skillDictionary[skill] || [];
                if (aliases.some(alias => containsPhrase(line.toLowerCase(), alias.toLowerCase()))) {
                    (preferredMode ? preferred : required).add(skill);
                }
            });
        });

        // Skills mentioned without an identifiable section are treated as required
        // so they are not silently ignored.
        jobSkills.forEach(skill => {
            if (!preferred.has(skill) && !required.has(skill)) required.add(skill);
        });

        const preferredOnly = [...preferred].filter(skill => !required.has(skill));
        return { required: [...required], preferred: preferredOnly };
    }

    function getResumeSections(text) {
        const checks = [
            { name: "Contact details", ok: /[\w.+-]+@[\w.-]+\.[a-z]{2,}/i.test(text) || /(\+?\d[\d\s().-]{7,}\d)/.test(text) },
            { name: "Education", ok: /\b(education|degree|university|college|bachelor|master|b\.?tech|bca|mca)\b/i.test(text) },
            { name: "Projects", ok: /\b(projects?|developed|built|implemented|created)\b/i.test(text) },
            { name: "Experience", ok: /\b(experience|internship|employment|worked at|work history)\b/i.test(text) },
            { name: "Measurable impact", ok: /\b\d+%|\b\d+\+|\b\d+\s*(users|customers|projects|members|hours|days|weeks)\b/i.test(text) }
        ];
        return checks;
    }

    function getWeakPhrases(text) {
        const lower = text.toLowerCase();
        return Object.entries(weakPhrases)
            .filter(([phrase]) => containsPhrase(lower, phrase))
            .map(([phrase, suggestion]) => ({ phrase, suggestion }));
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function renderTags(id, items, emptyMessage) {
        const el = document.getElementById(id);
        if (!el) return;
        el.replaceChildren();
        if (!items.length) {
            const span = document.createElement("span");
            span.textContent = emptyMessage;
            el.appendChild(span);
            return;
        }
        items.forEach(item => {
            const tag = document.createElement("span");
            tag.textContent = item;
            el.appendChild(tag);
        });
    }

    function renderList(id, items) {
        const el = document.getElementById(id);
        if (!el) return;
        el.replaceChildren();
        items.forEach(item => {
            const li = document.createElement("li");
            li.textContent = item;
            el.appendChild(li);
        });
    }

    function renderInsights(data) {
        const host = document.getElementById("jobFitRadar");
        if (!host) return;

        const requiredText = `${data.requiredMatched.length}/${data.required.length} required skills matched`;
        const preferredText = `${data.preferredMatched.length}/${data.preferred.length} preferred skills matched`;
        const sectionRows = data.sections.map(section =>
            `<div class="fit-check ${section.ok ? "is-ok" : "is-missing"}">
                <span>${section.ok ? "✓" : "＋"}</span><span>${section.name}</span>
                <strong>${section.ok ? "Detected" : "Not detected"}</strong>
            </div>`
        ).join("");

        host.innerHTML = `
            <div class="fit-radar-head">
                <div><span class="section-label">RESUMATE INTELLIGENCE</span><h3>Job Fit Radar</h3>
                <p>See what matched, what is missing and what to improve next.</p></div>
                <div class="fit-weight-note">Weighted skill match</div>
            </div>
            <div class="fit-summary-grid">
                <div class="fit-summary"><strong>${requiredText}</strong><span>Required skills</span></div>
                <div class="fit-summary"><strong>${preferredText}</strong><span>Preferred skills</span></div>
                <div class="fit-summary"><strong>${data.sectionScore}%</strong><span>Resume section checklist</span></div>
            </div>
            <h4>Resume completeness checklist</h4>
            <div class="fit-checklist">${sectionRows}</div>
            <p class="fit-explain">Score method: required skills carry 2 points and preferred skills carry 1 point. Section checks are separate indicators and do not change the ATS match score.</p>
        `;
    }

    function analyzeResume() {
        const resume = resumeInput.value.trim();
        const job = jobInput.value.trim();

        if (resume.length < 30) {
            alert("Please enter more resume content before analysis.");
            resumeInput.focus();
            return false;
        }
        if (job.length < 20) {
            alert("Please enter a complete job description.");
            jobInput.focus();
            return false;
        }

        const resumeSkills = extractSkills(resume);
        const jobSkills = extractSkills(job);

        if (!jobSkills.length) {
            alert("We could not detect supported skills in this job description. Add a skills or requirements section and try again.");
            jobInput.focus();
            return false;
        }

        const groups = classifyJobSkills(job, jobSkills);
        const matchedSkills = jobSkills.filter(skill => resumeSkills.includes(skill));
        const missingSkills = jobSkills.filter(skill => !resumeSkills.includes(skill));
        const requiredMatched = groups.required.filter(skill => resumeSkills.includes(skill));
        const preferredMatched = groups.preferred.filter(skill => resumeSkills.includes(skill));

        const totalWeight = groups.required.length * 2 + groups.preferred.length;
        const earnedWeight = requiredMatched.length * 2 + preferredMatched.length;
        const score = totalWeight ? Math.round((earnedWeight / totalWeight) * 100) : 0;

        const sections = getResumeSections(resume);
        const sectionScore = Math.round(sections.filter(item => item.ok).length / sections.length * 100);
        const weakMatches = getWeakPhrases(resume);

        setText("atsScore", score + "%");
        const progress = document.getElementById("scoreProgress");
        if (progress) progress.style.width = score + "%";
        setText("scoreMessage",
            score >= 80 ? "Strong keyword alignment. Review the missing items before applying." :
            score >= 60 ? "Good starting match. Prioritize the missing required skills." :
            score >= 40 ? "Partial match. Strengthen relevant skills and evidence in your resume." :
            "Low keyword alignment. Tailor your resume to the role before applying."
        );
        setText("matchedCount", matchedSkills.length);
        setText("missingCount", missingSkills.length);

        renderTags("matchedKeywords", matchedSkills, "No matching skills found");
        renderTags("missingKeywords", missingSkills, "No missing skills detected");

        const weakContainer = document.getElementById("weakVerbs");
        if (weakContainer) {
            weakContainer.replaceChildren();
            if (!weakMatches.length) {
                weakContainer.textContent = "No common weak phrases detected. Keep bullet points specific and measurable.";
            } else {
                weakMatches.forEach(item => {
                    const p = document.createElement("p");
                    p.textContent = `"${item.phrase}" → Try: ${item.suggestion}`;
                    weakContainer.appendChild(p);
                });
            }
        }

        const recommendations = [];
        if (groups.required.length) {
            const absentRequired = groups.required.filter(skill => !resumeSkills.includes(skill));
            if (absentRequired.length) recommendations.push("Priority: address these missing required skills only if you genuinely have them: " + absentRequired.join(", ") + ".");
            else recommendations.push("All detected required skills appear in your resume. Make sure each is supported by a project or experience example.");
        }
        const absentPreferred = groups.preferred.filter(skill => !resumeSkills.includes(skill));
        if (absentPreferred.length) recommendations.push("Optional advantage: consider adding relevant preferred skills you genuinely know: " + absentPreferred.join(", ") + ".");
        if (weakMatches.length) recommendations.push("Rewrite vague bullet points with clear action, tools used and outcome. Do not exaggerate your contribution.");
        if (!sections.find(s => s.name === "Measurable impact").ok) recommendations.push("Add genuine measurable outcomes where possible, such as time saved, records processed or users supported.");
        if (!sections.find(s => s.name === "Projects").ok) recommendations.push("Add 1–2 relevant projects and explain your individual contribution.");
        recommendations.push("Use the same terminology as the job description where it accurately describes your experience.");
        renderList("recommendations", recommendations);

        const analysis = {
            score, matchedSkills, missingSkills,
            required: groups.required, preferred: groups.preferred,
            requiredMatched, preferredMatched, sections, sectionScore
        };
        window.latestATSAnalysis = analysis;
        renderInsights(analysis);

        if (resultsSection) {
            resultsSection.hidden = false;
            setTimeout(() => resultsSection.scrollIntoView({ behavior: "smooth", block: "start" }), 100);
        }
        return true;
    }

    resumeForm.addEventListener("submit", async event => {
        event.preventDefault();
        const success = analyzeResume();
        if (success) await saveAnalysisToDatabase();
    });

    const reanalyzeBtn = document.getElementById("reanalyzeBtn");
    if (reanalyzeBtn) {
        reanalyzeBtn.addEventListener("click", () => {
            if (resultsSection) resultsSection.hidden = true;
            resumeForm.scrollIntoView({ behavior: "smooth", block: "center" });
        });
    }

    const printBtn = document.getElementById("printReportBtn");
    if (printBtn) printBtn.addEventListener("click", () => window.print());

    [["resumeText", ["resumeCount", "resumeCharCount"]],
     ["jobDescription", ["jdCount", "jobCount", "jobCharCount"]]].forEach(([inputId, counterIds]) => {
        const input = document.getElementById(inputId);
        if (!input) return;
        const update = () => counterIds.forEach(id => {
            const counter = document.getElementById(id);
            if (counter) counter.textContent = input.value.length + " characters";
        });
        input.addEventListener("input", update);
        update();
    });

    console.log("Resumate AI explainable ATS engine ready.");
});

async function saveAnalysisToDatabase() {
    const resume = document.getElementById("resumeText")?.value?.trim() || "";
    const job = document.getElementById("jobDescription")?.value?.trim() || "";
    const analysis = window.latestATSAnalysis;
    if (!analysis || !resume || !job) return;

    try {
        const response = await fetch("save-analysis.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                score: analysis.score,
                matched: analysis.matchedSkills.length,
                missing: analysis.missingSkills.length,
                resume,
                job
            })
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            console.warn("Analysis was not saved:", result.message || response.statusText);
        } else {
            console.log("ATS analysis saved.");
        }
    } catch (error) {
        console.error("Analysis history error:", error);
    }
}
