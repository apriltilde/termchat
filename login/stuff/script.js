var chatid = localStorage.getItem("lastChatId") || "main";
var chatname = localStorage.getItem("lastChatName" || "main");
var autocomp = ['namecolour', 'bgcolour', 'createdm', 'goto', 'info', 'clear', 'help'];

var chatIdDiv = document.getElementById('chatid');
chatIdDiv.textContent = chatname;

function getmsg(chatid) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "get.php?chatid=" + encodeURIComponent(chatid), true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 404) {
                window.location.href = "../";
            }
            if (xhr.status === 200) { 
                var response = JSON.parse(xhr.responseText);
                var messages = response.messages;
                var terminalOutput = document.querySelector('#b .terminal-output');
                terminalOutput.innerHTML = '';
                messages.forEach(function(message, index) {
                    var div = document.createElement('div');
                    div.setAttribute('data-index', index);
                    div.innerHTML = '<div><span style="font-weight: bold; color: ' + message.textcolour + '; background: ' + message.bgcolour + ';">' + message.username + '</span><span>: ' + message.msg + '</span><span style="float: right;"> ' + message.datetime + '</div></div>';


                    terminalOutput.appendChild(div);
                });
            }
        }
    };
    xhr.send();

    var xhrOnline = new XMLHttpRequest();
    xhrOnline.open("GET", "online.php", false);
    xhrOnline.onreadystatechange = function() {
        if (xhrOnline.readyState === XMLHttpRequest.DONE && xhrOnline.status === 200) {
            var response = JSON.parse(xhrOnline.responseText);
    var dmChatNames = response.dmChats.map(function(chat) {
        return chat.chatname;
    });
    
    // Extract chatnames from chats
    var chatNames = response.chats.map(function(chat) {
        return chat.chatname;
    });
			autocomp = autocomp.concat(dmChatNames).concat(chatNames).concat(response.username);
            var onlineUsersDiv = document.getElementById("onlineusers");
            var offlineUsersDiv = document.getElementById("offlineusers");

            // Clear previous content
            onlineUsersDiv.innerHTML = "";
            offlineUsersDiv.innerHTML = "";

            // Update online users div
            response.onlineUsers.forEach(function(user) {
                var userElement = document.createElement("p");
                userElement.textContent = user;
                onlineUsersDiv.appendChild(userElement);
            });

            // Update offline users div
            response.offlineUsers.forEach(function(user) {
                var userElement = document.createElement("p");
                userElement.textContent = user;
                offlineUsersDiv.appendChild(userElement);
            });
        var dmDiv = document.getElementById("dm");
        dmDiv.innerHTML = "";
        response.dmChats.forEach(function(chat) {
            var chatElement = document.createElement("p");
            chatElement.textContent = chat.chatname + (chat.notif > 0 ? " (" + chat.notif + ")" : "");
            dmDiv.appendChild(chatElement);
        });

        // Export non-DM chats to the "chats" div
        var chatsDiv = document.getElementById("chats");
        chatsDiv.innerHTML = "";
        response.chats.forEach(function(chat) {
            var chatElement = document.createElement("p");
            chatElement.textContent = chat.chatname + (chat.notif > 0 ? " (" + chat.notif + ")" : "");
            chatsDiv.appendChild(chatElement);
        });
        }
    };
    xhrOnline.send();
}

function checkchat(chat) {
    return new Promise((resolve, reject) => {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "checkchat.php?chat=" + encodeURIComponent(chat), true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    resolve(JSON.parse(xhr.responseText));
                } else {
                    reject(xhr.status);
                }
            }
        };
        xhr.send();
    });
}

function send(...msgs) {
        var concatenatedMsg = msgs.join(" ");
        var xhr = new XMLHttpRequest();
        var formData = "chatid=" + encodeURIComponent(chatid) + "&message=" + encodeURIComponent(concatenatedMsg);
        xhr.open("POST", "send.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send(formData);
        setTimeout(() => {
        getmsg(chatid);
      }, 100);
}	

$(document).ready(function () {
getmsg(chatid);
    setInterval(function() {
        getmsg(chatid);
    }, 4000);


var term = $('#a').terminal({
    namecolour: function(colour) {
        var xhr = new XMLHttpRequest();
        var url = "profile.php";
        var data = JSON.stringify({ "textcolour": colour });
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    term.echo("Username colour changed to " + colour);
                } else {
                    console.error("XHR request failed with status: " + xhr.status);
                }
            }
        };
        xhr.send(data);
        setTimeout(() => {
            getmsg(chatid);
        }, 100);
    },

info: function(username) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "info.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // Parse the JSON response
                var userInfo = JSON.parse(xhr.responseText);

                // Display user information or perform further actions
                term.echo("Username: " + "[[b;" + userInfo.textcolour + ";"+ userInfo.bgcolour +"]" + userInfo.username);
                term.echo("Last Active Time: " + userInfo.last_active_time);
                term.echo("Join Date: " + userInfo.joindate);
                term.echo("Messages Sent: " + userInfo.msg_count);
            } else {
                term.echo("User " + username + " not found")
            }
        }
    };
    // Send the request with the username as POST data
    xhr.send("username=" + encodeURIComponent(username));
},

    
goto: function(chat) {
    checkchat(chat)
    .then((response) => {
        chatid = response[0].chat;
        var chatIdDiv = document.getElementById('chatid');
        chatIdDiv.textContent = response[0].chatname;
        localStorage.setItem("lastChatId", chatid);
        localStorage.setItem("lastChatName", response[0].chatname);
        getmsg(chatid);
    })
    .catch((error) => {
        term.echo("Chat not found");
    });
},

    
quit: function() {
	window.location.href = "/";
},

    bgcolour: function(colour) {
        var xhr = new XMLHttpRequest();
        var url = "profile.php";
        var data = JSON.stringify({ "bgcolour": colour });
        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    term.echo("Background colour changed to " + colour);
                } else {
                    console.error("XHR request failed with status: " + xhr.status);
                }
            }
        };
        xhr.send(data);
        setTimeout(() => {
            getmsg(chatid);
        }, 100);
    },
createdm: function(dm) {
    var xhr = new XMLHttpRequest();
    var url = "add.php";
    var data = "username=" + encodeURIComponent(dm);
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                term.echo("Created dm with " + dm);
            }
            else if (xhr.status === 501) {
                term.echo("You already have a dm with " + dm);
            } else {
                term.echo("User not found");
            }
        }
    };
    xhr.send(data);
    setTimeout(() => {
        getmsg(chatid);
    }, 100);
},

help: function() {
    term.echo("Available Commands:");
    term.echo("  namecolour [colour]: Change username colour");
    term.echo("  bgcolour [colour]: Change background colour");
    term.echo("  createdm [username]: Create a dm with user");
    term.echo("  goto [chat]: Switch to the specified chat");
    term.echo("  info [username]: Get user information");
    term.echo("  clear: Clear the terminal output");
    term.echo("  quit: Disconnects the user from the server");
    term.echo("  help: Display this help message");
    
}
}, {
    greetings: 'cmd\nAvailable Commands:\n  namecolour [colour]: Change username colour\n  bgcolour [colour]: Change background colour\n  createdm [username]: Create a dm with user\n  goto [chat]: Switch to the specified chat\n  info [username]: Get user information\n  clear: Clear the terminal output\n  quit: Disconnects the user from the server\n  help: Display this help message',
    prompt: '>',
    autocompleteMenu: true,
    completion: autocomp
});

  
$('#b').terminal(function(command) {
        // Function to handle commands
        if (command !== '') {
          send(command);
        }
      }, {
        prompt: '>',
        greetings: ''
      });
});

