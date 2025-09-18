let tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
document.head.appendChild(tag);

let player;

function openVideo() {
  document.getElementById('videoOverlay').style.display = 'flex';
  
  if (!player) {
    // Attach API to iframe once
    player = new YT.Player('ytFrame', {
      events: {
        'onStateChange': onPlayerStateChange
      }
    });
  }
}

// Listen for video end
function onPlayerStateChange(event) {
  if (event.data === YT.PlayerState.ENDED) {
    closeVideo();
  }
}

function closeVideo() {
  document.getElementById('videoOverlay').style.display = 'none';
  if (player) {
    player.stopVideo(); // reset video
  }
}
