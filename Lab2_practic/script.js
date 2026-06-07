// main.js

document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const fioInput = document.getElementById('fio');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const commentInput = document.getElementById('comment');
    const formError = document.getElementById('formError');

    formError.style.display = 'none';
    formError.textContent = '';
    [fioInput, emailInput, phoneInput, commentInput].forEach(el => el.classList.remove('error'));

    let isValid = true;

    if (!fioInput.value.trim()) {
        fioInput.classList.add('error');
        isValid = false;
    }
    if (!emailInput.value.trim()) {
        emailInput.classList.add('error');
        isValid = false;
    }
    if (!phoneInput.value.trim()) {
        phoneInput.classList.add('error');
        isValid = false;
    }
    if (!commentInput.value.trim()) {
        commentInput.classList.add('error');
        isValid = false;
    }

    // 2. Валидация формата E-mail
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (emailInput.value.trim() && !emailRegex.test(emailInput.value.trim())) {
        emailInput.classList.add('error');
        isValid = false;
    }

    // 3. Валидация формата телефона 
    const phoneRegex = /^[\d\s()+-]{7,20}$/;
    if (phoneInput.value.trim() && !phoneRegex.test(phoneInput.value.trim())) {
        phoneInput.classList.add('error');
        isValid = false;
    }

    if (!isValid) {
        formError.textContent = 'Пожалуйста, заполните все поля корректно.';
        formError.style.display = 'block';
        return;
    }

    // Отправка данных аяксом
    const formData = new FormData(form);

    fetch('submit.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            form.style.display = 'none';
            document.getElementById('resLast').textContent = data.lastName;
            document.getElementById('resFirst').textContent = data.firstName;
            document.getElementById('resMiddle').textContent = data.middleName;
            document.getElementById('resEmail').textContent = data.email;
            document.getElementById('resPhone').textContent = data.phone;
            document.getElementById('resContactTime').textContent = data.contactTime;
            document.getElementById('successMessage').style.display = 'block';
        } else {
            formError.textContent = data.message;
            formError.style.display = 'block';
        }
    })
    .catch(error => {
        formError.textContent = 'Произошла ошибка при отправке запроса.';
        formError.style.display = 'block';
        console.error(error);
    });
});