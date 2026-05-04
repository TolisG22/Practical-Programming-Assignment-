// --- Form Validation για την Εγγραφή ---
const regForm = document.getElementById('regForm');
if (regForm) {
    regForm.addEventListener('submit', function(event) {
        let pass = document.getElementById('password').value;
        let user = document.getElementById('username').value;

        // Έλεγχος μήκους κωδικού με SweetAlert2
        if (pass.length < 6) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Πρόβλημα στον κωδικό',
                text: 'Ο κωδικός πρέπει να είναι τουλάχιστον 6 χαρακτήρες!',
                confirmButtonColor: '#333'
            });
        }

        // Έλεγχος αν το username είναι κενό
        else if (user.trim() === "") {
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Κενό Username',
                text: 'Παρακαλώ συμπληρώστε ένα όνομα χρήστη.',
            });
        }
    });
}

// Interactivity για το Like 
document.addEventListener('click', function(e) {
    // Χρησιμοποιούμε closest για να πιάνει το κλικ ακόμα κι αν πατήσει πάνω στο κείμενο
    const btn = e.target.closest('.like-btn');
    
    if (btn) {
        const recipeId = btn.getAttribute('data-id');

        fetch('like.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ recipe_id: recipeId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'liked') {
                btn.innerHTML = "❤️ Liked";
                btn.classList.add('active'); // Προσθήκη κλάσης για CSS styling
                
                // Notification (Toast)
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Προστέθηκε στα αγαπημένα!'
                });
            } else {
                btn.innerHTML = "🤍 Like";
                btn.classList.remove('active');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Σφάλμα', 'Κάτι πήγε στραβά με το Like.', 'error');
        });
    }
});

// Interactivity για τα Σχόλια
document.addEventListener('keypress', function(e) {
    if (e.target && e.target.classList.contains('comment-box') && e.key === 'Enter') {
        const recipeId = e.target.getAttribute('data-recipe'); // Παίρνουμε το ID από το attribute
        const commentText = e.target.value;
        const inputField = e.target;

        if (commentText.trim() === "") return;

        fetch('add_comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ recipe_id: recipeId, text: commentText })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Προσθήκη του σχολίου στο HTML αμέσως
                const container = document.getElementById(`comments-container-${recipeId}`);
                const commentDiv = document.createElement('div');
                commentDiv.className = 'single-comment';
                commentDiv.innerHTML = `<strong>${data.user}:</strong> ${commentText}`;
                container.appendChild(commentDiv);
                
                inputField.value = ""; // Καθαρισμός

                // Μικρό notification
                Swal.fire({
                    icon: 'success',
                    title: 'Το σχόλιο προστέθηκε!',
                    timer: 1000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        });
    }
});

let currentSlide = 0;

function moveSlide(direction) {
    const slides = document.querySelectorAll('.recipe-slide');
    
    // Κρύβουμε τα πάντα και αφαιρούμε κλάσεις
    slides.forEach(slide => {
        slide.classList.remove('active', 'flipped', 'upcoming');
        slide.style.display = "none";
    });

    // Υπολογισμός νέου slide
    currentSlide = (currentSlide + direction + slides.length) % slides.length;

    // Εμφάνιση του τρέχοντος
    slides[currentSlide].style.display = "block";
    slides[currentSlide].classList.add('active');

    // Προετοιμασία επόμενου
    let nextIndex = (currentSlide + 1) % slides.length;
    slides[nextIndex].style.display = "block";
    slides[nextIndex].classList.add('upcoming');
}


// 2. Λειτουργία Αναζήτησης στο Slider
function filterRecipes() {
    let input = document.getElementById('recipeSearch').value.toLowerCase();
    let slides = document.querySelectorAll('.recipe-slide');
    let foundIndex = -1;

    // Αν η μπάρα είναι άδεια, μην κάνεις τίποτα ή γύρνα στην πρώτη
    if (input === "") return;

    slides.forEach((slide, index) => {
        let title = slide.querySelector('h3').innerText.toLowerCase();
        
        if (title.includes(input)) {
            if (foundIndex === -1) foundIndex = index; // Κρατάμε την πρώτη που βρήκαμε
        }
    });

    if (foundIndex !== -1) {
        // Ενημερώνουμε τον παγκόσμιο δείκτη του slider
        currentSlide = foundIndex;

        // Καθαρίζουμε όλες τις κλάσεις από όλα τα slides για να μην "κολλάνε"
        slides.forEach(slide => {
            slide.classList.remove('active', 'flipped', 'upcoming');
            slide.style.display = "none"; // Τα κρύβουμε προσωρινά όλα
        });

        // Εμφανίζουμε μόνο αυτό που βρήκαμε
        slides[foundIndex].style.display = "block";
        slides[foundIndex].classList.add('active');
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // 1. Παίρνουμε το ID από το URL
    const urlParams = new URLSearchParams(window.location.search);
    const recipeId = urlParams.get('id');

    if (recipeId) {
        const slides = document.querySelectorAll('.recipe-slide');
        let targetIndex = -1;

        // 2. Ψάχνουμε σε ποιο slide αντιστοιχεί αυτό το ID
        slides.forEach((slide, index) => {
            if (slide.getAttribute('data-id') === recipeId) {
                targetIndex = index;
            }
        });

        // 3. Αν το βρούμε, κάνουμε αυτό το slide ενεργό
        if (targetIndex !== -1) {
            // Αφαιρούμε το active από το πρώτο slide (που μπαίνει default)
            slides.forEach(s => {
                s.classList.remove('active');
                s.style.display = "none";
            });

            // Εμφανίζουμε το σωστό slide
            currentSlide = targetIndex; // Ενημερώνουμε τη μεταβλητή του slider
            slides[currentSlide].classList.add('active');
            slides[currentSlide].style.display = "block";
            
            // Scroll ομαλά μέχρι το βιβλίο για να το δει ο χρήστης
            slides[currentSlide].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});

