module.exports = {
  content: [
      './templates/**/*.html.twig', // Analysez tous les fichiers Twig
      './assets/**/*.js', // Analysez les fichiers JS dans "assets"
      './assets/**/*.vue', // (Optionnel) Pour Vue.js si utilisé
  ],
  theme: {
      extend: {},
  },
  plugins: [],
};
