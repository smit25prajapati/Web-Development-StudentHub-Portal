const courseContainer = document.getElementById("courseContainer");
const searchInput = document.getElementById("searchInput");
const departmentFilter = document.getElementById("departmentFilter");
const levelFilter = document.getElementById("levelFilter");
const sortSelect = document.getElementById("sortSelect");
const courseCount = document.getElementById("courseCount");
const noResults = document.getElementById("noResults");
const errorMessage = document.getElementById("errorMessage");

let allCourses = [];

// Fetch course data from the external JSON file.
fetch("courses.json")
    .then(response => {
        if (!response.ok) {
            throw new Error("Could not load courses.json");
        }
        return response.json();
    })
    .then(data => {
        allCourses = data;
        createFilterOptions(allCourses);
        updateCourses();
    })
    .catch(error => {
        console.error(error);
        errorMessage.hidden = false;
        courseCount.textContent = "";
    });

// Create department and level options from the JSON data.
function createFilterOptions(courses) {
    const departments = [...new Set(courses.map(course => course.department))].sort();
    const levels = [...new Set(courses.map(course => course.level))].sort();

    departments.forEach(department => {
        const option = document.createElement("option");
        option.value = department;
        option.textContent = department;
        departmentFilter.appendChild(option);
    });

    levels.forEach(level => {
        const option = document.createElement("option");
        option.value = level;
        option.textContent = level;
        levelFilter.appendChild(option);
    });
}

// Apply search, filters and sorting, then render the result.
function updateCourses() {
    const searchText = searchInput.value.toLowerCase().trim();
    const selectedDepartment = departmentFilter.value;
    const selectedLevel = levelFilter.value;
    const selectedSort = sortSelect.value;

    let filteredCourses = allCourses.filter(course => {
        const matchesSearch =
            course.name.toLowerCase().includes(searchText) ||
            course.department.toLowerCase().includes(searchText) ||
            course.description.toLowerCase().includes(searchText);

        const matchesDepartment =
            selectedDepartment === "all" ||
            course.department === selectedDepartment;

        const matchesLevel =
            selectedLevel === "all" ||
            course.level === selectedLevel;

        return matchesSearch && matchesDepartment && matchesLevel;
    });

    if (selectedSort === "name-asc") {
        filteredCourses.sort((a, b) => a.name.localeCompare(b.name));
    } else if (selectedSort === "name-desc") {
        filteredCourses.sort((a, b) => b.name.localeCompare(a.name));
    } else if (selectedSort === "duration-asc") {
        filteredCourses.sort((a, b) => a.duration - b.duration);
    } else if (selectedSort === "duration-desc") {
        filteredCourses.sort((a, b) => b.duration - a.duration);
    }

    renderCourses(filteredCourses);
}

// Generate course cards dynamically from JavaScript objects.
function renderCourses(courses) {
    courseContainer.innerHTML = "";

    courseCount.textContent =
        `${courses.length} course${courses.length === 1 ? "" : "s"} found`;

    noResults.hidden = courses.length !== 0;

    courses.forEach(course => {
        const card = document.createElement("article");
        card.className = "course-card";

        card.innerHTML = `
            <span class="badge">${course.level}</span>
            <h3>${course.name}</h3>
            <p>${course.description}</p>
            <div class="course-meta">
                <span>${course.department}</span>
                <span>${course.duration} years</span>
            </div>
        `;

        courseContainer.appendChild(card);
    });
}

// Run the same processing whenever the user changes a control.
searchInput.addEventListener("input", updateCourses);
departmentFilter.addEventListener("change", updateCourses);
levelFilter.addEventListener("change", updateCourses);
sortSelect.addEventListener("change", updateCourses);
