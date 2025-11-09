// This function will run when the 'players.php' page is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Only run this code if we are on the players page and the #live-player-list exists
    const playerListDiv = document.getElementById('live-player-list');
    if (playerListDiv) {
        
        // Function to fetch and update the live player list
        const fetchLivePlayers = () => {
            fetch('api/live_players.php')
                .then(response => response.json())
                .then(data => {
                    updatePlayerList(data);
                })
                .catch(error => {
                    console.error('Error fetching live players:', error);
                    playerListDiv.innerHTML = '<p class="error">Could not load live players.</p>';
                });
        };

        // Function to update the DOM with new player data
        const updatePlayerList = (data) => {
            const playerCountSpan = document.getElementById('player-count');
            
            if (data.error) {
                playerListDiv.innerHTML = `<p class="error">${data.error}</p>`;
                playerCountSpan.textContent = '0';
                return;
            }

            playerCountSpan.textContent = data.length;

            if (data.length === 0) {
                playerListDiv.innerHTML = '<p>No players are currently online.</p>';
                return;
            }

            // Build table
            let table = '<table><thead><tr><th>UUID</th><th>Name</th></tr></thead><tbody>';
            data.forEach(player => {
                table += `<tr><td>${player.uuid}</td><td>${player.name}</td></tr>`;
            });
            table += '</tbody></table>';
            
            playerListDiv.innerHTML = table;
        };

        // Fetch immediately on page load
        fetchLivePlayers();

        // Function to update the DOM with new player data
        const updatePlayerList = (data) => {
            const playerCountSpan = document.getElementById('player-count');
            
            if (data.error) {
                playerListDiv.innerHTML = `<p class="error">${data.error}</p>`;
                playerCountSpan.textContent = '0';
                return;
            }

            playerCountSpan.textContent = data.length;

            if (data.length === 0) {
                playerListDiv.innerHTML = '<p>No players are currently online.</p>';
                return;
            }

            // Build table
            let table = '<table><thead><tr><th>UUID</th><th>Name</th><th>Action</th></tr></thead><tbody>'; // Added Action column
            data.forEach(player => {
                table += `
                    <tr>
                        <td>${player.uuid}</td>
                        <td>${player.name}</td>
                        <td>
                            <button 
                                class="kick-btn" 
                                data-name="${player.name}"
                                style="background: #dc3545; color: white; border: none; padding: 5px; cursor: pointer;"
                            >Kick</button>
                        </td>
                    </tr>
                `;
            });
            table += '</tbody></table>';
            
            playerListDiv.innerHTML = table;
        };
        
        // ... (Initial fetch and setInterval are unchanged) ...
        fetchLivePlayers();
        setInterval(fetchLivePlayers, 10000);
    }

    // --- NEW Event Listener for Kick Button ---
    // We listen on the document body because the buttons are added dynamically
    document.body.addEventListener('click', (e) => {
        if (e.target.classList.contains('kick-btn')) {
            const playerName = e.target.dataset.name;
            const serverId = document.body.dataset.serverId; // Get server ID from <body> tag
            
            if (!serverId) {
                alert('Error: No server selected.');
                return;
            }
            
            if (confirm(`Are you sure you want to kick ${playerName}?`)) {
                kickPlayer(playerName, serverId, e.target);
            }
        }
    });

    // --- NEW Function to handle the kick API call ---
    const kickPlayer = (name, serverId, buttonElement) => {
        buttonElement.disabled = true; // Disable button to prevent double-click
        buttonElement.textContent = 'Kicking...';

        fetch('api/kick_player.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name: name })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Refresh the player list immediately
                const playerListDiv = document.getElementById('live-player-list');
                if (playerListDiv && playerListDiv.fetchLivePlayers) {
                     playerListDiv.fetchLivePlayers();
                } else {
                    // Fallback if we can't find the function
                    location.reload();
                }
            } else {
                alert(`Error: ${data.error}`);
                buttonElement.disabled = false;
                buttonElement.textContent = 'Kick';
            }
        })
        .catch(err => {
            console.error(err);
            alert('A client-side error occurred.');
            buttonElement.disabled = false;
            buttonElement.textContent = 'Kick';
        });
    };

    // --- Small fix: Expose fetchLivePlayers for our kick function ---
    if (playerListDiv) {
        // ...
        const fetchLivePlayers = () => { /* ... */ };
        playerListDiv.fetchLivePlayers = fetchLivePlayers; // Attach function to the element
        // ...
        const updatePlayerList = (data) => { /* ... */ };
        
        playerListDiv.fetchLivePlayers(); // Call it
        setInterval(playerListDiv.fetchLivePlayers, 10000); // And here
    }

});