function goHomePage(){
    location.href = "/";
}

function switchLogoutRed(){
    document.getElementById("logouticon").src = "/pageelements/logout_red.png";
}

function switchLogoutDefault(){
    document.getElementById("logouticon").src = "/pageelements/logout.png";
}

function addVideoToLiked(userThatLiked, idOfVideo, titleOfVideo, lengthOfVideo, uploaderOfVideo, uploaderPicOfVideo){
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "/services/liked_video_add.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.send("user=" + encodeURIComponent(userThatLiked) + "&videoid=" + encodeURIComponent(idOfVideo) + "&videotitle=" + encodeURIComponent(titleOfVideo) + "&videolength=" + encodeURIComponent(lengthOfVideo) + "&videouploader=" + encodeURIComponent(uploaderOfVideo) + "&videouploaderpic=" + encodeURIComponent(uploaderPicOfVideo));

    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log("Video hozzaadva kedveltekhez!");
            document.getElementById("likedvideostatus-modal").innerHTML = `
            <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-body" id="likedvideostatus-body">
                    <img src="/pageelements/green_likebutton.png" width="70" height="auto"/><br><br>
                    <h4>Videó hozzáadva a kedveltekhez!</h4>
                </div>
                </div>
            </div>
            </div>`;
            var myModal = new bootstrap.Modal(document.getElementById('exampleModalCenter'));
            myModal.show();
            setTimeout(function() {
                myModal.hide();
            }, 2000);
        }
    };
}
