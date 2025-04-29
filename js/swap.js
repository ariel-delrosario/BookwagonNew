document.addEventListener('DOMContentLoaded', function() {
    loadMyBooks();
    loadAvailableBooks();
    
    // Initialize modal
    const addBookModal = new bootstrap.Modal(document.getElementById('addBookModal'));
    
    // Add button click handler
    document.querySelector('[data-bs-target="#addBookModal"]').addEventListener('click', function() {
        addBookModal.show();
    });

    // Search functionality
    document.getElementById('searchBooks').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        filterBooks(searchTerm);
    });
    
    // Condition filter buttons
    document.querySelectorAll('.filters .btn').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.filters .btn').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            filterByCondition(this.dataset.condition);
        });
    });

    document.getElementById('submitBook').addEventListener('click', function() {
        const form = document.getElementById('addBookForm');
        const formData = new FormData(form);
        
        fetch('api/add_book.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addBookModal.hide(); // Use the modal instance
                form.reset();
                loadMyBooks();
                loadAvailableBooks();
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });

    // Prevent button clicks from triggering card flip
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
            e.stopPropagation();
        }
    }, true);
});

function loadMyBooks() {
    fetch('api/list_my_books.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('myBooksContainer');
            if (data.books.length === 0) {
                container.innerHTML = `
                    <div class="col-12">
                        <div class="no-books-message">
                            <h4>You haven't added any books yet</h4>
                            <p>Click the "Add Book for Swap" button to get started!</p>
                        </div>
                    </div>
                `;
                return;
            }
            renderBooks(data.books, container, true);
        });
}

function loadAvailableBooks() {
    fetch('api/list_available_books.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('availableBooksContainer');
            if (data.books.length === 0) {
                container.innerHTML = `
                    <div class="col-12">
                        <div class="no-books-message">
                            <h4>No books available for swap</h4>
                            <p>Check back later for more books!</p>
                        </div>
                    </div>
                `;
                return;
            }
            renderBooks(data.books, container, false);
        });
}

function renderBooks(books, container, isMyBooks) {
    container.innerHTML = books.map(book => `
        <div class="col-md-3 mb-4" data-condition="${book.condition}" data-title="${book.book_title.toLowerCase()}">
            <div class="book-card">
                <div class="book-card-inner">
                    <!-- Front of card -->
                    <div class="book-card-front">
                        <div class="book-image-container">
                            <img src="${book.image_path}" class="book-image w-100" alt="${book.book_title}">
                            <div class="flip-hint">
                                <i class="fas fa-undo"></i> Click to see description
                            </div>
                        </div>
                        <div class="book-details">
                            <span class="book-condition">${book.condition}</span>
                            <h5 class="book-title">${book.book_title}</h5>
                            <p class="book-author">By ${book.author}</p>
                            
                            ${isMyBooks ? 
                                `<button class="btn btn-outline-danger btn-sm" onclick="removeBook(${book.id})">
                                    <i class="fas fa-trash"></i> Remove
                                </button>` :
                                `<div class="action-container">
                                    <button class="btn swap-btn btn-sm" onclick="initiateSwap(${book.id})">
                                        <i class="fas fa-exchange-alt"></i> Swap
                                    </button>
                                    <div class="book-owner">
                                        <img src="${book.user_avatar || 'images/default-avatar.png'}" class="owner-avatar" alt="Owner">
                                        <span class="owner-info">Owned by ${book.firstname} ${book.lastname}</span>
                                    </div>
                                </div>`
                            }
                        </div>
                    </div>
                    
                    <!-- Back of card -->
                    <div class="book-card-back">
                        <h5 class="mb-4">About this book</h5>
                        <p class="book-description-back">${book.description || 'No description available.'}</p>
                        <div class="mt-auto">
                            <small class="text-muted">Click again to flip back</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    // Add click event listeners for flipping
    container.querySelectorAll('.book-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                return;
            }
            this.classList.toggle('flipped');
        });
    });
}

function filterBooks(searchTerm) {
    const books = document.querySelectorAll('#availableBooksContainer .col-md-3');
    books.forEach(book => {
        const title = book.dataset.title;
        book.style.display = title.includes(searchTerm) ? '' : 'none';
    });
}

function filterByCondition(condition) {
    const books = document.querySelectorAll('#availableBooksContainer .col-md-3');
    books.forEach(book => {
        if (condition === 'all' || book.dataset.condition === condition) {
            book.style.display = '';
        } else {
            book.style.display = 'none';
        }
    });
}

function initiateSwap(bookId) {
    // Implement swap functionality
    alert('Swap request feature coming soon!');
}