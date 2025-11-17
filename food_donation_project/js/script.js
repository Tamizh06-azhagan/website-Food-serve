document.addEventListener("DOMContentLoaded", async function () {

  // =============================
  // Home - Get Started
  // =============================
  const startButton = document.querySelector(".hero .btn");
  if (startButton) {
    startButton.addEventListener("click", function (e) {
      e.preventDefault();
      window.location.href = "login.html";
    });
  }

  // =============================
  // Login Handling
  // =============================
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const email = document.getElementById("email").value.trim();
      const password = document.getElementById("password").value.trim();

      if (!email || !password) {
        alert("❌ Please fill all fields!");
        return;
      }

      try {
        const response = await fetch("http://localhost/food_donation_project/backend/login.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ email, password }),
        });

        const result = await response.json();
        console.log("Login Response:", result);

        if (result.status === "success") {
          localStorage.setItem("user_id", result.id);
          localStorage.setItem("role", result.role);

          if (result.role === "donor") {
            window.location.href = "donation.html";
          } else {
            window.location.href = "orphanage.html";
          }
        } else {
          alert("❌ " + result.message);
        }
      } catch (err) {
        alert("⚠️ Backend not reachable.");
      }
    });
  }

  // =============================
  // Donor Signup
  // =============================
  const donorSignupForm = document.getElementById("donorSignupForm");
  if (donorSignupForm) {
    donorSignupForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const name = document.getElementById("name").value.trim();
      const age = document.getElementById("age").value.trim();
      const contact = document.getElementById("contact").value.trim();
      const email = document.getElementById("email").value.trim();
      const password = document.getElementById("password").value.trim();
      const location = document.getElementById("location").value.trim();

      if (!name || !age || !contact || !email || !password || !location) {
        alert("❌ Please fill all fields!");
        return;
      }

      try {
        const response = await fetch("http://localhost/food_donation_project/backend/donor_signup.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ name, age, contact, email, password, location }),
        });

        const result = await response.json();

        if (result.status === "success") {
          alert("✅ Signup Successful!");
          window.location.href = "login.html";
        } else {
          alert("❌ " + result.message);
        }
      } catch (err) {
        alert("⚠️ Unable to connect to server!");
      }
    });
  }

  // =============================
  // Donation Form (Donor)
  // =============================
  const donateForm = document.getElementById("donateForm");
  const popup = document.getElementById("successPopup");

  if (donateForm) {
    donateForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const donorId = localStorage.getItem("user_id");
      if (!donorId) {
        alert("⚠️ Please log in first!");
        window.location.href = "login.html";
        return;
      }

      const foodName = document.getElementById("foodName").value.trim();
      const quantity = document.getElementById("quantity").value.trim();
      const location = document.getElementById("location").value.trim();
      const contact = document.getElementById("contact").value.trim();
     const preparedTime = document.getElementById("preparedTime")?.value 
    || new Date().toISOString().slice(0,19);

      if (!foodName || !quantity || !location || !contact) {
        alert("❌ All fields are required!");
        return;
      }

      const phonePattern = /^[0-9]{10}$/;
      if (!phonePattern.test(contact)) {
        alert("📞 Enter a valid 10-digit number!");
        return;
      }

      try {
        const response = await fetch("http://localhost/food_donation_project/backend/save_donation.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            donor_id: donorId,
            foodName,
            quantity,
            location,
            contact,
            preparedTime,
          }),
        });

        const result = await response.json();

        if (result.status === "success") {
          popup.style.display = "flex";
          setTimeout(() => (popup.style.display = "none"), 3000);
          donateForm.reset();
        } else {
          alert("❌ " + result.message);
        }
      } catch (err) {
        alert("⚠️ Server connection error!");
      }
    });
  }

  // =============================
  // Orphanage Dashboard
  // =============================
  const donationList = document.getElementById("donationList");
  const acceptedList = document.getElementById("acceptedList");

  function hoursSince(prep) {
    if (!prep) return 999;

    // handles both formats
    const dt = prep.includes("T") ? prep : prep.replace(" ", "T");
    const prepTime = new Date(dt);
    const now = new Date();

    return (now - prepTime) / 3600000;
  }

  if (donationList && acceptedList) loadOrphanageDonations();

  async function fetchDonations() {
    const response = await fetch("http://localhost/food_donation_project/backend/get_donation.php");
    const result = await response.json();
    return result.status === "success" ? result.data : [];
  }

  async function acceptDonationInDB(donationId, orphanageId) {
    const response = await fetch("http://localhost/food_donation_project/backend/accept_donation.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ donation_id: donationId, accepted_by: orphanageId }),
    });

    const result = await response.json();
    return result.status === "success";
  }

  async function loadOrphanageDonations() {
    const orphanageId = localStorage.getItem("user_id");

    donationList.innerHTML = "Loading...";
    acceptedList.innerHTML = "Loading...";

    let all = await fetchDonations();

    // Filter: available within 6 hours
    const available = all.filter(d => {
      if (d.status === "accepted") return false;
      return hoursSince(d.prepared_time) <= 6;
    });

    // Accepted donations
    const accepted = all.filter(d =>
      d.status === "accepted" && String(d.accepted_by) === String(orphanageId)
    );

    // Render Available
    donationList.innerHTML = "";
    if (available.length === 0) {
      donationList.innerHTML = "<p>No available donations.</p>";
    }

    available.forEach(item => {
      const card = document.createElement("div");
      card.className = "donation-card";
      card.innerHTML = `
        <h3>${item.food_name}</h3>
        <p><strong>Quantity:</strong> ${item.quantity}</p>
        <p><strong>Location:</strong> ${item.location}</p>
        <p><strong>Contact:</strong> ${item.contact}</p>
        <p><strong>Prepared Time:</strong> ${item.prepared_time}</p>
        <button class="accept-btn">Accept</button>
      `;

      card.querySelector(".accept-btn").onclick = async () => {
        const ok = await acceptDonationInDB(item.id, orphanageId);
        if (ok) loadOrphanageDonations();
        else alert("❌ Could not accept donation!");
      };

      donationList.appendChild(card);
    });

    // Render Accepted
    acceptedList.innerHTML = "";
    if (accepted.length === 0) {
      acceptedList.innerHTML = "<p>No accepted donations.</p>";
    }

    accepted.forEach(item => {
      const card = document.createElement("div");
      card.className = "donation-card";
      card.innerHTML = `
        <h3>${item.food_name}</h3>
        <p><strong>Quantity:</strong> ${item.quantity}</p>
        <p><strong>Location:</strong> ${item.location}</p>
        <p><strong>Contact:</strong> ${item.contact}</p>
        <p><strong>Prepared Time:</strong> ${item.prepared_time}</p>
        <span class="accepted-label">Accepted</span>
      `;
      acceptedList.appendChild(card);
    });
  }
});

// =============================
// Global Accept Handler
// =============================
window.acceptDonation = async function (donationId, button) {
  const orphanageId = localStorage.getItem("user_id");
  if (!orphanageId) {
    alert("⚠️ Please login again.");
    window.location.href = "login.html";
    return;
  }

  const response = await fetch("http://localhost/food_donation_project/backend/accept_donation.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ donation_id: donationId, accepted_by: orphanageId }),
  });

  const result = await response.json();

  if (result.status === "success") {
    button.innerText = "Accepted";
    button.disabled = true;
    button.style.backgroundColor = "green";
  } else {
    alert("❌ " + result.message);
  }
};
