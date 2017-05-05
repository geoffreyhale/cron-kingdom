// This object will be sent every time you submit a message in the sendMessage function.
const clientInformation = {
    username: chat_username
    // You can add more information in a static object
};

// START SOCKET CONFIG
const conn = new WebSocket(chat_webSocketUrl);

conn.onopen = function(e) {
    console.info("Connection established successfully");
};

conn.onmessage = function(e) {
    let data = JSON.parse(e.data);
    Chat.appendMessage(data.username, data.message);

    //        console.log(data);
};

conn.onerror = function(e){
    alert("Error: something went wrong with the socket.");
    console.error(e);
};
// END SOCKET CONFIG


/// Some code to add the messages to the list element and the message submit.
document.getElementById("form-submit").addEventListener("click",function(){
    const msg = document.getElementById("form-message").value;

    if(!msg){
        alert("Please send something on the chat");
    }

    Chat.sendMessage(msg);
    // Empty text area
    document.getElementById("form-message").value = "";
}, false);

// Mini API to send a message with the socket and append a message in a UL element.
const Chat = {
    appendMessage: function(username,message){
        // Append List Item
        const ul = document.getElementById("chat-history");
        const li = document.createElement("li");
        li.appendChild(document.createTextNode(clientInformation.username + " : "+ message));
        ul.appendChild(li);
    },
    sendMessage: function(text){
        clientInformation.message = text;
        // Send info as JSON
        conn.send(JSON.stringify(clientInformation));
        // Add my own message to the list
        this.appendMessage(clientInformation.username, clientInformation.message);
    }
};