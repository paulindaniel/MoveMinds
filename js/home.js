document.addEventListener("DOMContentLoaded", function() {
  // Carrossel
  const prevButton = document.querySelector(".carousel .prev");
  const nextButton = document.querySelector(".carousel .next");
  const carouselImages = document.querySelector(".carousel-images");
  const images = document.querySelectorAll(".carousel .carousel-images .carousel-image-wrapper");
  const captions = document.querySelectorAll(".carousel .carousel-images .image-caption");
  const totalImages = images.length;
  let currentIndex = 0;

  function showImage(index) {
      const offset = -index * 100;
      carouselImages.style.transform = `translateX(${offset}%)`;
      captions.forEach((caption, i) => {
          caption.style.display = i === index ? 'block' : 'none';
      });
  }

  nextButton.addEventListener("click", function() {
      currentIndex = (currentIndex + 1) % totalImages;
      showImage(currentIndex);
  });

  prevButton.addEventListener("click", function() {
      currentIndex = (currentIndex - 1 + totalImages) % totalImages;
      showImage(currentIndex);
  });

  showImage(currentIndex);

  // Sistema de Desafios
  const challengesList = document.getElementById('challengesList');
  const addChallengeBtn = document.querySelector('.add-challenge-btn');
  const challengeModal = document.getElementById('challengeModal');
  const closeModalBtn = document.querySelector('.close-btn');
  const challengeForm = document.getElementById('challengeForm');
  const challengeTypeSelect = document.getElementById('challengeType');
  const modalTitle = document.getElementById('modalTitle');
  
  // Carregar desafios do localStorage ou usar dados padrão
  let challenges = JSON.parse(localStorage.getItem('challenges')) || [
      {
          id: 1,
          name: "CORRER 5 KM",
          type: "progress",
          currentValue: 3,
          targetValue: 5,
          progress: 60
      },
      {
          id: 2,
          name: "CHECK-IN ACADEMIA",
          type: "count",
          currentValue: 5,
          targetValue: 7
      },
      {
          id: 3,
          name: "BEBER 18L DE ÁGUA",
          type: "progress",
          currentValue: 5.4,
          targetValue: 18,
          progress: 30
      }
  ];

  // Carregar desafios ao iniciar
  renderChallenges();

  // Abrir modal para adicionar novo desafio
  addChallengeBtn.addEventListener('click', () => {
      challengeForm.reset();
      document.getElementById('challengeId').value = '';
      modalTitle.textContent = 'Adicionar Novo Desafio';
      challengeModal.style.display = 'flex';
  });

  // Fechar modal
  closeModalBtn.addEventListener('click', () => {
      challengeModal.style.display = 'none';
  });

  // Fechar modal ao clicar fora
  window.addEventListener('click', (e) => {
      if (e.target === challengeModal) {
          challengeModal.style.display = 'none';
      }
  });

  // Salvar desafio
  challengeForm.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const id = document.getElementById('challengeId').value;
      const name = document.getElementById('challengeName').value;
      const type = document.getElementById('challengeType').value;
      const currentValue = parseFloat(document.getElementById('currentValue').value);
      const targetValue = parseFloat(document.getElementById('targetValue').value);
      
      if (type === 'progress') {
          const progress = Math.round((currentValue / targetValue) * 100);
          
          if (id) {
              // Editar desafio existente
              const index = challenges.findIndex(c => c.id == id);
              challenges[index] = { 
                  id: parseInt(id), 
                  name, 
                  type, 
                  currentValue, 
                  targetValue, 
                  progress 
              };
          } else {
              // Adicionar novo desafio
              const newId = challenges.length > 0 ? Math.max(...challenges.map(c => c.id)) + 1 : 1;
              challenges.push({ 
                  id: newId, 
                  name, 
                  type, 
                  currentValue, 
                  targetValue, 
                  progress 
              });
          }
      } else {
          if (id) {
              // Editar desafio existente
              const index = challenges.findIndex(c => c.id == id);
              challenges[index] = { 
                  id: parseInt(id), 
                  name, 
                  type, 
                  currentValue, 
                  targetValue 
              };
          } else {
              // Adicionar novo desafio
              const newId = challenges.length > 0 ? Math.max(...challenges.map(c => c.id)) + 1 : 1;
              challenges.push({ 
                  id: newId, 
                  name, 
                  type, 
                  currentValue, 
                  targetValue 
              });
          }
      }
      
      saveChallenges();
      renderChallenges();
      challengeModal.style.display = 'none';
  });

  // Funções auxiliares
  function saveChallenges() {
      localStorage.setItem('challenges', JSON.stringify(challenges));
  }

  function renderChallenges() {
      challengesList.innerHTML = '';
      
      challenges.forEach(challenge => {
          const challengeEl = document.createElement('div');
          challengeEl.className = 'challenge-item';
          challengeEl.dataset.id = challenge.id;
          
          if (challenge.type === 'progress') {
              challengeEl.innerHTML = `
                  <div class="challenge-item-header">
                      <p class="challenge-item-name">${challenge.name}</p>
                      <div class="challenge-item-actions">
                          <button class="edit-btn" data-id="${challenge.id}">Editar</button>
                          <button class="delete-btn" data-id="${challenge.id}">Remover</button>
                      </div>
                  </div>
                  <div class="progress-container">
                      <div class="progress-bar" role="progressbar" aria-valuenow="${challenge.progress}" aria-valuemin="0" aria-valuemax="100">
                          <div class="progress-fill" style="width: ${challenge.progress}%;"></div>
                      </div>
                      <span class="progress-text">${challenge.currentValue}/${challenge.targetValue} (${challenge.progress}%)</span>
                  </div>
                  <div class="progress-controls">
                      <input type="number" class="progress-input" placeholder="Adicionar progresso" min="0" max="${challenge.targetValue - challenge.currentValue}">
                      <button class="update-progress-btn" data-id="${challenge.id}">Atualizar</button>
                  </div>
              `;
          } else {
              challengeEl.innerHTML = `
                  <div class="challenge-item-header">
                      <p class="challenge-item-name">${challenge.name}</p>
                      <div class="challenge-item-actions">
                          <button class="edit-btn" data-id="${challenge.id}">Editar</button>
                          <button class="delete-btn" data-id="${challenge.id}">Remover</button>
                      </div>
                  </div>
                  <div class="progress-container">
                      <p>${challenge.currentValue}/${challenge.targetValue}</p>
                  </div>
                  <div class="count-controls">
                      <button class="increment-btn" data-id="${challenge.id}">+1</button>
                  </div>
              `;
          }
          
          challengesList.appendChild(challengeEl);
      });
      
      // Adicionar event listeners para os botões de edição e remoção
      document.querySelectorAll('.edit-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
              const id = e.target.dataset.id;
              editChallenge(id);
          });
      });
      
      document.querySelectorAll('.delete-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
              const id = e.target.dataset.id;
              deleteChallenge(id);
          });
      });
      
      // Adicionar event listeners para atualização de progresso
      document.querySelectorAll('.update-progress-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
              const id = e.target.dataset.id;
              const input = e.target.previousElementSibling;
              const value = parseFloat(input.value);
              
              if (!isNaN(value) && value >= 0) {
                  updateProgress(id, value);
                  input.value = '';
              }
          });
      });
      
      // Adicionar event listeners para incremento de contador
      document.querySelectorAll('.increment-btn').forEach(btn => {
          btn.addEventListener('click', (e) => {
              const id = e.target.dataset.id;
              incrementCount(id);
          });
      });
  }

  function editChallenge(id) {
      const challenge = challenges.find(c => c.id == id);
      
      if (challenge) {
          document.getElementById('challengeId').value = challenge.id;
          document.getElementById('challengeName').value = challenge.name;
          document.getElementById('challengeType').value = challenge.type;
          document.getElementById('currentValue').value = challenge.currentValue;
          document.getElementById('targetValue').value = challenge.targetValue;
          
          modalTitle.textContent = 'Editar Desafio';
          challengeModal.style.display = 'flex';
      }
  }

  function deleteChallenge(id) {
      if (confirm('Tem certeza que deseja remover este desafio?')) {
          challenges = challenges.filter(c => c.id != id);
          saveChallenges();
          renderChallenges();
      }
  }

  function updateProgress(id, value) {
      const index = challenges.findIndex(c => c.id == id);
      
      if (index !== -1 && challenges[index].type === 'progress') {
          challenges[index].currentValue += value;
          
          // Garantir que não ultrapasse o valor alvo
          if (challenges[index].currentValue > challenges[index].targetValue) {
              challenges[index].currentValue = challenges[index].targetValue;
          }
          
          // Calcular novo progresso
          challenges[index].progress = Math.round((challenges[index].currentValue / challenges[index].targetValue) * 100);
          
          saveChallenges();
          renderChallenges();
      }
  }

  function incrementCount(id) {
      const index = challenges.findIndex(c => c.id == id);
      
      if (index !== -1 && challenges[index].type === 'count') {
          challenges[index].currentValue += 1;
          
          // Garantir que não ultrapasse o valor alvo
          if (challenges[index].currentValue > challenges[index].targetValue) {
              challenges[index].currentValue = challenges[index].targetValue;
          }
          
          saveChallenges();
          renderChallenges();
      }
  }
});