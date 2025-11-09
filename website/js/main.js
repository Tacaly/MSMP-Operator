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
        
        // Then, fetch every 10 seconds
        setInterval(fetchLivePlayers, 10000);
    }
});