document.addEventListener('DOMContentLoaded', function() {
    loadAvailableBooks();
    loadMyBooks();
    
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
                bootstrap.Modal.getInstance(document.getElementById('addBookModal')).hide();
                form.reset();
                loadAvailableBooks();
                loadMyBooks();
        } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    });
    
    // Handle swap request form submission
    document.getElementById('swapRequestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitButton = document.getElementById('submitSwapRequest');
        const spinner = document.getElementById('submitSwapSpinner');
        const buttonText = document.getElementById('submitSwapText');
        
        // Disable button and show spinner
                    submitButton.disabled = true;
        spinner.classList.remove('d-none');
        buttonText.textContent = 'Processing...';
        
        const formData = new FormData(this);

        fetch('api/request_swap.php', {
                        method: 'POST',
                        body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('swapRequestModal')).hide();
                alert('Swap request sent successfully!');
                // Reload swap requests
                loadSwapRequests();
                    } else {
                alert(data.message || 'Failed to send swap request. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        })
        .finally(() => {
            // Re-enable button and hide spinner
                    submitButton.disabled = false;
            spinner.classList.add('d-none');
            buttonText.textContent = 'Send Swap Request';
        });
    });

    // Add event listeners for the tabs
    document.getElementById('requests-tab').addEventListener('click', function() {
    loadSwapRequests();
    });
    
    document.getElementById('my-books-tab').addEventListener('click', function() {
        loadMyBooks();
    });
});

function loadAvailableBooks() {
    fetch('api/get_available_books.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById('availableBooksContainer');
                container.innerHTML = '';
                
                if (data.books.length === 0) {
                    container.innerHTML = `
                        <div class="col-12">
                            <div class="no-books-message">
                                <h4>No books available for swap</h4>
                                <p>Be the first to add a book for swapping!</p>
                            </div>
                        </div>`;
                    return;
                }
                
                data.books.forEach(book => {
                    const colDiv = document.createElement('div');
                    colDiv.className = 'col-md-3 mb-4';
                    
                    colDiv.innerHTML = `
                        <div class="book-card">
                            <img src="${book.image_path}" class="book-image w-100" alt="${book.book_title}">
                            <div class="card-body p-3">
                                <h5 class="card-title">${book.book_title}</h5>
                                <p class="card-text">By ${book.author}</p>
                                <p class="card-text"><small>Condition: ${book.condition}</small></p>
                                <p class="card-text"><small>Owner: ${book.firstname} ${book.lastname}</small></p>
                                <button class="btn swap-btn w-100" onclick="initiateSwap(${book.id})">Request Swap</button>
                            </div>
                        </div>`;
                    
                    container.appendChild(colDiv);
                });
            } else {
                console.error('Error loading books:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function loadMyBooks() {
    fetch('api/list_my_books.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
            const container = document.getElementById('myBooksContainer');
                const select = document.getElementById('myBookSelect');
                
                container.innerHTML = '';
                if (select) {
                    // Clear previous options except the first one
                    select.innerHTML = '<option value="">Select a book to offer</option>';
            }
            
            if (data.books.length === 0) {
                container.innerHTML = `
                    <div class="col-12">
                        <div class="no-books-message">
                            <h4>You haven't added any books yet</h4>
                                <p>Add books to your collection to start swapping!</p>
                        </div>
                        </div>`;
                    return;
                }
                
                data.books.forEach(book => {
                    // Add to my books container
                    const colDiv = document.createElement('div');
                    colDiv.className = 'col-md-3 mb-4';
                    
                    colDiv.innerHTML = `
                        <div class="book-card">
                            <img src="${book.image_path}" class="book-image w-100" alt="${book.book_title}">
                            <div class="card-body p-3">
                                <h5 class="card-title">${book.book_title}</h5>
                                <p class="card-text">By ${book.author}</p>
                                <p class="card-text"><small>Condition: ${book.condition}</small></p>
                                <button class="btn btn-danger w-100 mb-2" onclick="deleteBook(${book.id})">Remove</button>
                    </div>
                        </div>`;
                    
                    container.appendChild(colDiv);
                    
                    // Add to select dropdown in swap modal
                    if (select) {
                        const option = document.createElement('option');
                        option.value = book.id;
                        option.textContent = `${book.book_title} (${book.condition})`;
                        select.appendChild(option);
                    }
                });
            } else {
                console.error('Error loading my books:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function loadSwapRequests() {
    fetch('api/get_swap_requests.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Process incoming requests
                const incomingContainer = document.getElementById('incomingRequestsContainer');
                incomingContainer.innerHTML = '';
                
                if (data.incoming_requests.length === 0) {
                    incomingContainer.innerHTML = `
                        <div class="text-center py-4">
                            <p>No incoming swap requests at the moment.</p>
                        </div>`;
                } else {
                    data.incoming_requests.forEach(request => {
                        const requestDiv = document.createElement('div');
                        requestDiv.className = 'request-card';
                        
                        const statusClass = request.status === 'pending' ? 'status-pending' : 
                                         request.status === 'accepted' ? 'status-accepted' : 'status-rejected';
                        
                        requestDiv.innerHTML = `
                            <div class="request-header">
                                <img src="${request.requester_avatar}" class="requester-avatar" alt="${request.requester_name}">
                                <div class="request-info">
                                    <div class="requester-name">${request.requester_name}</div>
                                    <div class="request-date">${new Date(request.created_at).toLocaleString()}</div>
                                </div>
                                <span class="status-badge ${statusClass}">${request.status}</span>
                            </div>
                            <div class="request-book">
                                <img src="${request.book_image}" class="request-book-image" alt="${request.book_title}">
                                <div class="request-book-info">
                                    <h5>${request.book_title}</h5>
                                    <p>Condition: ${request.book_condition}</p>
                        </div>
                    </div>
                            ${request.message ? `<div class="request-message">"${request.message}"</div>` : ''}
                            ${request.status === 'pending' ? `
                                <div class="request-actions">
                                    <button class="btn-accept" onclick="handleSwapRequest(${request.id}, 'accept')">Accept Swap</button>
                                    <button class="btn-reject" onclick="handleSwapRequest(${request.id}, 'reject')">Decline</button>
                                </div>
                            ` : ''}
                            ${request.status === 'accepted' ? `
                                <div class="request-actions">
                                    <div id="logistics-info-${request.id}">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            ` : ''}
                        `;
                        
                        incomingContainer.appendChild(requestDiv);

                        // Load logistics info for accepted requests
                        if (request.status === 'accepted') {
                            fetch(`api/get_logistics.php?request_id=${request.id}`)
                                .then(response => response.json())
                                .then(logistics => {
                                    const logisticsContainer = document.getElementById(`logistics-info-${request.id}`);
                                    if (logistics.success && logistics.data) {
                                        logisticsContainer.innerHTML = `
                                            <div class="logistics-info mt-3 p-3 bg-light rounded">
                                                <h6>Delivery Arrangement:</h6>
                                                <p><strong>Method:</strong> ${logistics.data.delivery_method}</p>
                                                <p><strong>When:</strong> ${new Date(logistics.data.scheduled_date).toLocaleString()}</p>
                                                <p><strong>Where:</strong> ${logistics.data.location}</p>
                                                ${logistics.data.notes ? `<p><strong>Notes:</strong> ${logistics.data.notes}</p>` : ''}
                                                <button class="btn btn-sm btn-outline-primary" onclick="showLogisticsModal(${request.id})">
                                                    Update Arrangement
                                                </button>
                                            </div>`;
                                    } else {
                                        logisticsContainer.innerHTML = `
                                            <div class="mt-3">
                                                <button class="btn btn-primary" onclick="showLogisticsModal(${request.id})">
                                                    Set Up Delivery
                                                </button>
                                            </div>`;
                                    }
                                })
                                .catch(error => {
                                    console.error('Error loading logistics:', error);
                                    document.getElementById(`logistics-info-${request.id}`).innerHTML = `
                                        <div class="alert alert-danger mt-3">
                                            Error loading delivery details
                                        </div>`;
                                });
                        }
                    });
                }
                
                // Process outgoing requests
                const outgoingContainer = document.getElementById('outgoingRequestsContainer');
                outgoingContainer.innerHTML = '';
                
                if (data.outgoing_requests.length === 0) {
                    outgoingContainer.innerHTML = `
                        <div class="text-center py-4">
                            <p>You haven't sent any swap requests yet.</p>
                        </div>`;
                } else {
                    data.outgoing_requests.forEach(request => {
                        const requestDiv = document.createElement('div');
                        requestDiv.className = 'request-card';
                        
                        const statusClass = request.status === 'pending' ? 'status-pending' : 
                                         request.status === 'accepted' ? 'status-accepted' : 'status-rejected';
                        
                        requestDiv.innerHTML = `
                            <div class="request-header">
                                <img src="${request.owner_avatar}" class="requester-avatar" alt="${request.owner_name}">
                                <div class="request-info">
                                    <div class="requester-name">${request.owner_name}</div>
                                    <div class="request-date">${new Date(request.created_at).toLocaleString()}</div>
                                </div>
                                <span class="status-badge ${statusClass}">${request.status}</span>
                            </div>
                            <div class="request-book">
                                <img src="${request.book_image}" class="request-book-image" alt="${request.book_title}">
                                <div class="request-book-info">
                                    <h5>${request.book_title}</h5>
                                    <p>Condition: ${request.book_condition}</p>
                                </div>
                            </div>
                            ${request.message ? `<div class="request-message">"${request.message}"</div>` : ''}
                            ${request.status === 'pending' ? `
                                <div class="request-actions">
                                    <button class="btn-reject" onclick="cancelRequest(${request.id})">Cancel Request</button>
                        </div>
                            ` : ''}
                        `;
                        
                        outgoingContainer.appendChild(requestDiv);
                    });
                }
            } else {
                console.error('Error loading swap requests:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading swap requests:', error);
            document.getElementById('incomingRequestsContainer').innerHTML = `
                <div class="text-center py-4">
                    <p>Error loading requests. Please try again later.</p>
                </div>`;
            document.getElementById('outgoingRequestsContainer').innerHTML = `
                <div class="text-center py-4">
                    <p>Error loading requests. Please try again later.</p>
                </div>`;
        });
}

function initiateSwap(bookId) {
    // Get book details to display in the modal
    fetch(`api/get_book_details.php?id=${bookId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const book = data.book;
                
                // Update modal content with book information
                document.getElementById('requestedBookInfo').innerHTML = `
                    <img src="${book.image_path}" alt="${book.book_title}" style="width: 100px; margin-right: 15px;">
                    <div>
                        <h5>${book.book_title}</h5>
                        <p>By ${book.author}</p>
                        <p><small>Condition: ${book.condition}</small></p>
                        <p><small>Owner: ${book.firstname} ${book.lastname}</small></p>
                    </div>
                `;
                
                // Set the book ID in the hidden field - FIXED THIS LINE
                document.getElementById('book_id').value = bookId;
                
                // Show the modal
                const swapModal = new bootstrap.Modal(document.getElementById('swapRequestModal'));
                swapModal.show();
                
                // Load user's books for the dropdown
                loadMyBooks();
            } else {
                alert('Error loading book details. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
}

function handleSwapRequest(requestId, action) {
    if (!confirm(`Are you sure you want to ${action} this swap request?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('action', action);
    
    fetch('api/handle_swap_request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            if (action === 'accept') {
                // Show logistics modal only for accepted requests
                showLogisticsModal(requestId);
            } else {
                loadSwapRequests();
            }
        } else {
            alert(data.message || `Failed to ${action} request. Please try again.`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function cancelRequest(requestId) {
    if (!confirm('Are you sure you want to cancel this swap request?')) {
        return;
    }
    
    // Since the API doesn't have a specific cancel endpoint, we'll use the handle_swap_request with reject action
    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('action', 'reject');
    
    fetch('api/handle_swap_request.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
            alert('Swap request cancelled successfully!');
            loadSwapRequests();
            } else {
            alert(data.message || 'Failed to cancel request. Please try again.');
            }
        })
        .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function deleteBook(bookId) {
    if (!confirm('Are you sure you want to remove this book from your swap list?')) {
        return;
    }

    // Note: You'll need to create a delete_book.php endpoint as it's not in the provided APIs
    fetch('api/delete_book.php', {
                method: 'POST',
                headers: {
            'Content-Type': 'application/json',
                },
        body: JSON.stringify({
            book_id: bookId
        })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
            alert('Book removed successfully!');
            loadMyBooks();
            loadAvailableBooks();
                    } else {
            alert(data.message || 'Failed to remove book. Please try again.');
                }
            })
            .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function showLogisticsModal(requestId) {
    // Create and show the modal
    const modalHTML = `
    <div class="modal fade" id="logisticsModal" tabindex="-1" aria-labelledby="logisticsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logisticsModalLabel">Arrange Delivery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="logisticsForm">
                        <input type="hidden" name="request_id" value="${requestId}">
                        
                        <div class="mb-3">
                            <label class="form-label">Delivery Method</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_method" id="pickupMethod" value="pickup" checked>
                                <label class="form-check-label" for="pickupMethod">Pick Up</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_method" id="meetupMethod" value="meetup">
                                <label class="form-check-label" for="meetupMethod">Meet Up</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="scheduledDate" class="form-label">Scheduled Date & Time</label>
                            <input type="datetime-local" class="form-control" id="scheduledDate" name="scheduled_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="Enter address or meeting point" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any special instructions"></textarea>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Details</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>`;
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize modal
    const logisticsModal = new bootstrap.Modal(document.getElementById('logisticsModal'));
    logisticsModal.show();
    
    // Handle form submission
    document.getElementById('logisticsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('api/handle_logistics.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Delivery details saved successfully!');
                logisticsModal.hide();
                loadSwapRequests(); // Refresh the requests list
            } else {
                alert(data.message || 'Failed to save delivery details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
    
    // Remove modal when closed
    document.getElementById('logisticsModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}