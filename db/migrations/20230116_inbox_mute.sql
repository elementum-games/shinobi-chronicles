-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 17, 2023 at 02:29 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

ALTER TABLE convos_users 
ADD muted int(11) NOT NULL DEFAULT 0;