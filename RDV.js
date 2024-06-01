document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".voir-plus").forEach((button) => {
    button.addEventListener("click", function () {
      toggleDetails(
        this,
        this.dataset.adresse,
        this.dataset.clientId,
        this.dataset.commentaires
      );
    });
  });

  document.querySelectorAll(".voir-agent").forEach((button) => {
    button.addEventListener("click", function () {
      toggleAgentDetails(this, this.dataset.agentId);
    });
  });

  document.querySelectorAll(".contact-buttons button").forEach((button) => {
    button.addEventListener("click", function () {
      var action = button.getAttribute("onclick").split("(")[0];
      if (action === "showChatRoom") {
        showChatRoom(button);
      } else if (action === "showEmailForm") {
        showEmailForm(button);
      } else if (action === "showChatRoomhistorique") {
        showChatRoomhistorique(button);
      }
    });
  });
});

function toggleDetails(button, adresse, clientId, commentaires) {
  var details = button.parentElement.nextElementSibling;
  var initialPhoto =
    button.parentElement.previousElementSibling.querySelector(".initial-photo");
  if (details.style.display === "none" || details.style.display === "") {
    details.style.display = "block";
    if (initialPhoto) {
      initialPhoto.style.display = "none";
    }
    button.innerText = "Voir moins";

    // Mettre à jour le formulaire caché avec l'adresse, le client ID et les commentaires
    document.getElementById("form_adresse").value = adresse;
    document.getElementById("form_client_id").value = clientId;
    document.getElementById("form_commentaires").value = commentaires;
  } else {
    details.style.display = "none";
    if (initialPhoto) {
      initialPhoto.style.display = "block";
    }
    button.innerText = "Voir plus";
  }
}

function toggleAgentDetails(button, agentId) {
  var agentDetails = button.nextElementSibling;
  if (
    agentDetails.style.display === "none" ||
    agentDetails.style.display === ""
  ) {
    agentDetails.style.display = "block";
    button.innerText = "Voir moins d'agent";

    // Mettre à jour le formulaire caché avec l'agent ID
    document.getElementById("form_agent_id").value = agentId;
  } else {
    agentDetails.style.display = "none";
    button.innerText = "Voir l'agent assigné";
  }
}

function showContactDetails(button) {
  var contactButtons = button
    .closest(".agent-info")
    .querySelector(".contact-buttons");
  if (contactButtons.style.display === "block") {
    contactButtons.style.display = "none";
  } else {
    contactButtons.style.display = "block";
  }
}

function scheduleAppointment(
  disponibilités,
  agentId,
  clientId,
  propriétéId,
  adresse,
  commentaires
) {
  const scheduleContainer = event.target
    .closest(".agent-details")
    .querySelector(".schedule");
  const scheduleList = scheduleContainer.querySelector(".schedule-list");

  if (scheduleContainer.style.display === "block") {
    scheduleContainer.style.display = "none";
    return;
  }

  scheduleList.innerHTML = "";

  const dayMap = {};

  disponibilités.forEach((dispo) => {
    if (!dayMap[dispo.jour]) {
      const dayTitle = document.createElement("div");
      dayTitle.className = "day-title";
      dayTitle.textContent = dispo.jour;
      dayTitle.onclick = () => toggleDay(dispo.jour);
      scheduleList.appendChild(dayTitle);

      dayMap[dispo.jour] = [];
    }

    const listItem = document.createElement("li");
    listItem.textContent = dispo.créneau_horaire;
    listItem.setAttribute("data-day", dispo.jour);
    listItem.setAttribute("data-dispo-id", dispo.id); // Ajouter l'ID de la disponibilité
    listItem.style.display = "none";
    listItem.onclick = () =>
      selectTimeSlot(
        listItem,
        agentId,
        clientId,
        propriétéId,
        adresse,
        commentaires
      );

    dayMap[dispo.jour].push(listItem);
  });

  Object.keys(dayMap).forEach((day) => {
    dayMap[day].forEach((item) => scheduleList.appendChild(item));
  });

  scheduleContainer.style.display = "block";
}

function toggleDay(selectedDay) {
  const dayItems = document.querySelectorAll(
    `.schedule-list li[data-day="${selectedDay}"]`
  );
  const firstDayItem = dayItems[0];

  if (firstDayItem.style.display === "block") {
    const allItems = document.querySelectorAll(".schedule-list li");
    allItems.forEach((item) => {
      item.style.display = "none";
    });

    const allTitles = document.querySelectorAll(".day-title");
    allTitles.forEach((title) => {
      title.style.display = "block";
    });
  } else {
    const allItems = document.querySelectorAll(".schedule-list li");
    allItems.forEach((item) => {
      item.style.display = "none";
    });

    dayItems.forEach((item) => {
      item.style.display = "block";
    });

    const allTitles = document.querySelectorAll(".day-title");
    allTitles.forEach((title) => {
      title.style.display = "block";
      if (title.textContent !== selectedDay) {
        title.style.display = "none";
      }
    });
  }
}

function selectTimeSlot(
  listItem,
  agentId,
  clientId,
  propriétéId,
  adresse,
  commentaires
) {
  const allItems = document.querySelectorAll(".schedule-list li");
  allItems.forEach((item) => {
    item.classList.remove("selected");
  });

  listItem.classList.add("selected");

  const existingButtons = document.querySelector(".confirmation-buttons");
  if (existingButtons) {
    existingButtons.remove();
  }

  const buttonsContainer = document.createElement("div");
  buttonsContainer.className = "confirmation-buttons";

  const confirmButton = document.createElement("button");
  confirmButton.className = "confirm";
  confirmButton.textContent = "Confirmer";
  confirmButton.onclick = () =>
    confirmAppointment(
      listItem,
      agentId,
      clientId,
      propriétéId,
      adresse,
      commentaires
    );

  const cancelButton = document.createElement("button");
  cancelButton.className = "cancel";
  cancelButton.textContent = "Annuler";
  cancelButton.onclick = () => cancelSelection(listItem);

  buttonsContainer.appendChild(confirmButton);
  buttonsContainer.appendChild(cancelButton);

  listItem.appendChild(buttonsContainer);
}

function confirmAppointment(
  listItem,
  agentId,
  clientId,
  propriétéId,
  adresse,
  commentaires
) {
  const selectedDay = listItem.getAttribute("data-day");
  const selectedTime = listItem.textContent;
  const dispoId = listItem.getAttribute("data-dispo-id");

  const currentDate = new Date();
  const nextDate = getNextDate(currentDate, selectedDay);

  document.getElementById("form_agent_id").value = agentId;
  document.getElementById("form_client_id").value = clientId;
  document.getElementById("form_propriété_id").value = propriétéId;
  document.getElementById("form_adresse").value = adresse;
  document.getElementById("form_date").value = nextDate
    .toISOString()
    .split("T")[0];
  document.getElementById("form_heure").value = selectedTime;
  document.getElementById("form_dispo_id").value = dispoId;
  document.getElementById("form_commentaires").value = commentaires;

  document.getElementById("appointmentForm").submit();
}

function cancelSelection(listItem) {
  listItem.classList.remove("selected");

  const buttonsContainer = listItem.querySelector(".confirmation-buttons");
  if (buttonsContainer) {
    buttonsContainer.remove();
  }
}

function getNextDate(currentDate, selectedDay) {
  const daysOfWeek = [
    "Dimanche",
    "Lundi",
    "Mardi",
    "Mercredi",
    "Jeudi",
    "Vendredi",
    "Samedi",
  ];
  const selectedIndex = daysOfWeek.indexOf(selectedDay);

  const resultDate = new Date(currentDate);
  resultDate.setDate(
    resultDate.getDate() + ((selectedIndex + 7 - currentDate.getDay()) % 7)
  );

  return resultDate;
}

function showChatRoom(button) {
  var agentId = button
    .closest(".agent-info")
    .querySelector(".agent-id").textContent;
  var clientId = "<?php echo $user['id']; ?>";
  window.location.href =
    "chatroom.php?agent_id=" + agentId + "&client_id=" + clientId;
}

function showEmailForm(button) {
  var agentId = button
    .closest(".agent-info")
    .querySelector(".agent-id").textContent;
  var clientId = "<?php echo $user['id']; ?>";
  window.location.href =
    "email_form.php?agent_id=" + agentId + "&client_id=" + clientId;
}

function showChatRoomhistorique(button) {
  var agentId = button
    .closest(".agent-info")
    .querySelector(".agent-id").textContent;
  var clientId = "<?php echo $user['id']; ?>";
  window.location.href =
    "historique.php?agent_id=" + agentId + "&client_id=" + clientId;
}

function loadChatMessages(agentId, clientId) {
  fetch("get_chat_messages.php?agent_id=" + agentId + "&client_id=" + clientId)
    .then((response) => response.json())
    .then((data) => {
      var chatMessages = document.getElementById("chatMessages");
      chatMessages.innerHTML = "";
      data.forEach((message) => {
        chatMessages.innerHTML +=
          "<p><strong>" +
          message.sender +
          ":</strong> " +
          message.message +
          "</p>";
      });
    });
}

document.getElementById("chatForm").addEventListener("submit", function (e) {
  e.preventDefault();
  var formData = new FormData(this);
  fetch("send_chat_message.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      loadChatMessages(formData.get("agent_id"), formData.get("client_id"));
      document.getElementById("chatMessage").value = "";
    });
});

function loadHistorique(agentId, clientId) {
  fetch("get_historique.php?agent_id=" + agentId + "&client_id=" + clientId)
    .then((response) => response.json())
    .then((data) => {
      var historiqueMessages = document.getElementById("historiqueMessages");
      historiqueMessages.innerHTML = "";
      data.forEach((record) => {
        historiqueMessages.innerHTML +=
          "<p>" +
          record.date +
          " " +
          record.heure +
          ": " +
          record.action +
          " - " +
          record.details +
          "</p>";
      });
    });
}

// Charger les messages de chat et l'historique au chargement de la page
document.addEventListener("DOMContentLoaded", function () {
  var agentId = document.getElementById("agent_id_chat")
    ? document.getElementById("agent_id_chat").value
    : null;
  var clientId = document.getElementById("client_id_chat")
    ? document.getElementById("client_id_chat").value
    : null;
  if (agentId && clientId) {
    loadChatMessages(agentId, clientId);
  }

  var agentIdHist = "<?php echo $agent_id; ?>";
  var clientIdHist = "<?php echo $client_id; ?>";
  if (agentIdHist && clientIdHist) {
    loadHistorique(agentIdHist, clientIdHist);
  }
});
