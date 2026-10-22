const resultTableBody = document.getElementById("resultTableBody");
const resultCount = document.getElementById("resultCount");
const resultError = document.getElementById("resultError");

fetch("courses.json")
    .then(response => {
        if (!response.ok) {
            throw new Error("Could not load courses.json");
        }

        return response.json();
    })
    .then(courses => {
        displayResults(courses);
    })
    .catch(error => {
        console.error(error);
        resultError.hidden = false;
        resultCount.textContent = "";
    });

function displayResults(courses) {
    resultTableBody.innerHTML = "";

    resultCount.textContent =
        `${courses.length} course results available`;

    courses.forEach(course => {
        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${course.name}</td>
            <td>${course.department}</td>
            <td>${course.level}</td>
            <td>${course.duration} years</td>
            <td class="status">Available</td>
        `;

        resultTableBody.appendChild(row);
    });
}
