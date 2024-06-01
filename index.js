const nodemailer = require("nodemailer");

const transporter = nodemailer.createTransport({
  host: "smtp.office365.com",
  port: 587,
  secure: false, // Utilisation de TLS (false pour STARTTLS)
  auth: {
    user: "omnesimmobilier@outlook.fr",
    pass: "asxdr1234",
  },
  tls: {
    ciphers: "SSLv3",
  },
});

const mailOptions = {
  from: "omnesimmobilier@outlook.fr",
  to: "jaxaji1330@adrais.com",
  subject: "email auto",
  text: "cet E-mail est auto",
};

transporter.sendMail(mailOptions, (error, info) => {
  if (error) {
    console.log(error);
  } else {
    console.log("Email envoyé " + info.response);
  }
});
