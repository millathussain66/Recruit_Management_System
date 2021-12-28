import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';
import Catagory from './components/user/categories/Category';
import FeaturedJob from './components/user/featured-jobs/FeaturedJob';
import Footer from './components/user/footer/Footer';
import Header from './components/user/header/Header';
import Jobs from './components/user/jobs/Jobs';

import './assets/style/main.scss';




ReactDOM.render(
  <React.StrictMode>
    <App />
    <Header/>
    <Catagory/>
    <FeaturedJob/>
    <Footer/>
    <Jobs/>
  
  </React.StrictMode>,
  document.getElementById('root')
);

