-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: learnora_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB
CREATE DATABASE IF NOT EXISTS learnora_db;
USE learnora_db;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','admin123');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `certificates` (
  `certificate_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `certificate_code` varchar(100) NOT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`certificate_id`),
  UNIQUE KEY `certificate_code` (`certificate_code`),
  UNIQUE KEY `unique_user_course_certificate` (`user_id`,`course_id`),
  KEY `fk_certificate_course` (`course_id`),
  CONSTRAINT `fk_certificate_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_certificate_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `certificates`
--

LOCK TABLES `certificates` WRITE;
/*!40000 ALTER TABLE `certificates` DISABLE KEYS */;
INSERT INTO `certificates` VALUES (1,1,1,'LRN-2026-7E1EAD6A','2026-09-27 19:15:08');
/*!40000 ALTER TABLE `certificates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `level` enum('Beginner','Intermediate','Advanced') DEFAULT 'Beginner',
  `duration` varchar(50) DEFAULT NULL,
  `instructor` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` VALUES 
(1,'Python Programming for Beginners','Programming','Learn Python programming from basics.','Python, Programming','Beginner','6 Weeks','Arun Kumar',NULL,'2026-09-27 18:22:03'),
(2,'Python for Data Science','Data Science','Learn Python, Pandas and NumPy for data science.','Python, Pandas, NumPy, Data Science','Beginner','8 Weeks','Dr. Meena',NULL,'2026-09-27 18:22:03'),
(3,'Machine Learning Fundamentals','AI & ML','Learn the basics of Machine Learning algorithms.','Python, Machine Learning, Scikit-learn','Intermediate','10 Weeks','Karthik Raj',NULL,'2026-09-27 18:22:03'),
(4,'Artificial Intelligence Basics','AI & ML','Learn the fundamentals of Artificial Intelligence.','AI, Python, Machine Learning','Beginner','6 Weeks','Dr. Anitha',NULL,'2026-09-27 18:22:03'),
(5,'Deep Learning with Python','AI & ML','Learn neural networks and deep learning.','Python, Deep Learning, Neural Networks','Advanced','12 Weeks','Vijay Kumar',NULL,'2026-09-27 18:22:03'),
(6,'Full Stack Web Development','Web Development','Learn frontend and backend web development.','HTML, CSS, JavaScript, PHP, MySQL','Intermediate','12 Weeks','Sanjay Kumar',NULL,'2026-09-27 18:22:03'),
(7,'HTML and CSS for Beginners','Web Development','Learn how to create modern websites.','HTML, CSS, Web Design','Beginner','4 Weeks','Divya',NULL,'2026-09-27 18:22:03'),
(8,'Java Programming','Programming','Learn Java and object-oriented programming.','Java, OOP, Programming','Intermediate','8 Weeks','Rajesh',NULL,'2026-09-27 18:22:03'),
(9,'SQL and Database Management','Database','Learn SQL and MySQL database management.','SQL, MySQL, Database','Beginner','5 Weeks','Manoj',NULL,'2026-09-27 18:22:03'),
(10,'UI UX Design Fundamentals','Design','Learn basic UI and UX design concepts.','UI Design, UX, Figma','Beginner','6 Weeks','Keerthi',NULL,'2026-09-27 18:22:03'),
(11,'Modern JavaScript Essentials','Web Development','Master core JavaScript (ES6+), DOM manipulation, async/await, and modern JS patterns.','JavaScript, JS, ES6, DOM, Async JS','Beginner','6 Weeks','Arun Kumar',NULL,'2026-09-28 01:25:00'),
(12,'React JS Frontend Development','Web Development','Build dynamic, interactive user interfaces with React JS, Hooks, State Management, and Redux Toolkit.','React, React JS, JavaScript, Hooks, Frontend','Intermediate','8 Weeks','Divya',NULL,'2026-09-28 01:25:00'),
(13,'PHP & Laravel Web Framework','Web Development','Learn modern backend web development with PHP 8, Laravel Framework, MVC architecture, and REST APIs.','PHP, Laravel, Backend, MySQL, REST API','Intermediate','8 Weeks','Sanjay Kumar',NULL,'2026-09-28 01:25:00'),
(14,'Cyber Security & Ethical Hacking','Cyber Security','Master network security, ethical hacking, vulnerability scanning, penetration testing, and web app security.','Cyber Security, Ethical Hacking, Network Security, Pen Testing','Beginner','8 Weeks','Karthik Raj',NULL,'2026-09-28 01:25:00'),
(15,'Network Security & Cryptography','Cyber Security','Explore data encryption, Public Key Infrastructure (PKI), SSL/TLS protocols, firewalls, and cyber defense strategy.','Cyber Security, Cryptography, Network Security, SSL, Firewalls','Advanced','10 Weeks','Dr. Anitha',NULL,'2026-09-28 01:25:00'),
(16,'Full-Stack React & Next.js','Web Development','Build server-rendered, high performance web apps using Next.js, Server Components, React 19, and Tailwind CSS.','React, Next.js, Full-Stack, Web Development, JavaScript','Advanced','10 Weeks','Divya',NULL,'2026-09-28 01:25:00'),
(17,'Cloud Computing & AWS Essentials','Cloud & DevOps','Learn Cloud Architecture, Amazon Web Services (AWS), EC2, S3, Lambda, and cloud deployment pipelines.','Cloud Computing, AWS, DevOps, Cloud Security, System Architecture','Intermediate','8 Weeks','Manoj',NULL,'2026-09-28 01:25:00'),
(18,'Data Structures & Algorithms in JS','Computer Science','Master arrays, linked lists, trees, graphs, sorting algorithms, and dynamic programming in JavaScript.','JavaScript, Data Structures, Algorithms, Problem Solving','Intermediate','8 Weeks','Dr. Meena',NULL,'2026-09-28 01:25:00');
UNLOCK TABLES;

--
-- Table structure for table `enrollments`
--

DROP TABLE IF EXISTS `enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Enrolled','Completed','Dropped') DEFAULT 'Enrolled',
  `progress` int(11) DEFAULT 0,
  PRIMARY KEY (`enrollment_id`),
  UNIQUE KEY `user_id` (`user_id`,`course_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollments`
--

LOCK TABLES `enrollments` WRITE;
/*!40000 ALTER TABLE `enrollments` DISABLE KEYS */;
INSERT INTO `enrollments` VALUES (1,1,1,'2026-09-27 18:53:17','Completed',100),(3,1,9,'2026-09-27 19:17:27','Enrolled',0);
/*!40000 ALTER TABLE `enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lesson_progress`
--

DROP TABLE IF EXISTS `lesson_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lesson_progress` (
  `progress_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`progress_id`),
  UNIQUE KEY `unique_user_lesson` (`user_id`,`lesson_id`),
  KEY `fk_progress_lesson` (`lesson_id`),
  CONSTRAINT `fk_progress_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_progress_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_progress`
--

LOCK TABLES `lesson_progress` WRITE;
/*!40000 ALTER TABLE `lesson_progress` DISABLE KEYS */;
INSERT INTO `lesson_progress` VALUES (1,1,1,1,'2026-09-27 19:14:23'),(4,1,2,1,'2026-09-27 19:14:40'),(5,1,3,1,'2026-09-27 19:14:50'),(6,1,4,1,'2026-09-27 19:14:57'),(7,1,5,1,'2026-09-27 19:15:04');
/*!40000 ALTER TABLE `lesson_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lessons` (
  `lesson_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `lesson_number` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `duration` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `document_title` varchar(255) DEFAULT NULL,
  `document_content` text DEFAULT NULL,
  `document_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`lesson_id`),
  UNIQUE KEY `unique_course_lesson` (`course_id`,`lesson_number`),
  CONSTRAINT `fk_lessons_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4385 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons`
--

LOCK TABLES `lessons` WRITE;
/*!40000 ALTER TABLE `lessons` DISABLE KEYS */;
INSERT INTO `lessons` VALUES (1,1,1,'Introduction to Python & Environment Setup','https://www.youtube.com/embed/rfscVS0vtbw','08:30','Overview of Python programming language, installation of Python 3, setting up VS Code, and running your first Hello World script.','📄 Python Setup & Hello World Study Notes','### 1. What is Python?\nPython is a high-level, interpreted language created by Guido van Rossum. It emphasizes code readability with clean, syntax-driven blocks.\n\n### 2. Installing Python & VS Code\n- Download Python 3.x from python.org.\n- Ensure \"Add Python to PATH\" is checked during installation.\n- Install VS Code and the Python Extension.\n\n### 3. Writing Your First Code\n```python\n# First Python Script\nprint(\"Hello, Learnora AI Student!\")\nname = input(\"Enter your name: \")\nprint(f\"Welcome to Python, {name}!\")\n```\n\n### Key Concepts:\n- Case-sensitivity: `name` and `Name` are distinct variables.\n- Comments begin with `#` symbol.',NULL,'2026-09-27 19:00:19'),(2,1,2,'Variables, Data Types & Basic Operators','https://www.youtube.com/embed/kqtD5dpn9C8','12:45','Master fundamental Python data types: Integers, Floats, Strings, Booleans, Lists, and Dictionaries alongside arithmetic operators.','📄 Data Types & Operators Cheat Sheet','### Primitive Data Types\n- **int**: Whole numbers e.g. `count = 42`\n- **float**: Decimals e.g. `price = 19.99`\n- **str**: Text enclosed in quotes e.g. `course = \"Python\"`\n- **bool**: Boolean truth values `is_active = True`\n\n### Data Structures\n```python\n# List (Ordered & Mutable)\nskills = [\"Python\", \"SQL\", \"JavaScript\"]\nskills.append(\"Git\")\n\n# Dictionary (Key-Value Pairs)\nstudent = {\"name\": \"Alex\", \"grade\": 95, \"passed\": True}\nprint(student[\"name\"])\n```\n\n### Arithmetic Operators\n- Addition (`+`), Subtraction (`-`), Multiplication (`*`), Division (`/`), Floor Division (`//`), Modulo (`%`).',NULL,'2026-09-27 19:00:19'),(3,1,3,'Control Flow: If-Else Conditions & Loops','https://www.youtube.com/embed/6iF8Xb7Z3wQ','15:20','Learn how to make decisions using if-elif-else statements and execute repetitive tasks with for and while loops.','📄 Conditions & Loops Reference Guide','### Conditional Logic\n```python\nscore = 88\nif score >= 90:\n    print(\"Grade: A\")\nelif score >= 80:\n    print(\"Grade: B\")\nelse:\n    print(\"Grade: C or below\")\n```\n\n### Iteration with For & While Loops\n```python\n# For Loop with range\nfor i in range(1, 6):\n    print(f\"Iteration #{i}\")\n\n# While Loop\ncount = 3\nwhile count > 0:\n    print(f\"Countdown: {count}\")\n    count -= 1\n```',NULL,'2026-09-27 19:00:19'),(4,1,4,'Python Functions & Scope','https://www.youtube.com/embed/9Os0o3wzS_I','18:10','Create modular reusable code using def functions, parameters, return values, and scope management.','📄 Functions & Scope Manual','### Defining Functions\n```python\ndef calculate_total(price, tax_rate=0.05):\n    \"\"\"Calculates final price with tax.\"\"\"\n    total = price * (1 + tax_rate)\n    return round(total, 2)\n\n# Function Calls\nprint(calculate_total(100))\nprint(calculate_total(200, 0.08))\n```\n\n### Key Principles\n- DRY (Don\'t Repeat Yourself)\n- Global vs Local Variable Scope',NULL,'2026-09-27 19:00:19'),(5,1,5,'Building a Python Command Line Mini Project','https://www.youtube.com/embed/8ext9G7xspg','22:30','Apply your skills to build a real-world CLI Student Grade & Course Management Application.','📄 Capstone Project Guide & Code','### Student Management System Project\nBuild a CLI app that prompts users to add students, enter scores, calculate class averages, and print top performers.',NULL,'2026-09-27 19:00:19'),(6,2,1,'Data Science Overview & Jupyter Notebooks Setup','https://www.youtube.com/embed/LHBE6Q9XlzI','10:15','Understand the Data Science lifecycle, set up Anaconda distribution and launch Jupyter Notebooks.','📄 Data Science Environment Setup','### Data Science Stack\n- Jupyter Lab / Notebooks\n- NumPy: Numerical matrix computing\n- Pandas: Dataframes & CSV processing\n- Matplotlib / Seaborn: Visualization\n\n### Jupyter Commands\n- Shift + Enter: Run cell and select below\n- Esc + M: Convert cell to Markdown\n- Esc + A: Insert cell above',NULL,'2026-09-27 19:00:19'),(7,2,2,'NumPy Arrays & Vectorized Computations','https://www.youtube.com/embed/QUT1VHiLmmI','14:50','Perform high-performance N-dimensional array manipulations, slicing, broadcasting, and matrix math.','📄 NumPy Matrix Reference Sheet','### Creating Arrays\n```python\nimport numpy as np\na = np.array([1, 2, 3, 4])\nb = np.zeros((3, 3))\nc = np.arange(0, 10, 2)\n```\n### Vectorized Operations\n```python\nvalues = np.array([10, 20, 30])\nresult = values * 2 + 5\nprint(\"Mean:\", np.mean(result))\n```',NULL,'2026-09-27 19:00:19'),(8,2,3,'Data Wrangling & Analysis with Pandas','https://www.youtube.com/embed/vmEHCJofslg','19:40','Load CSV datasets, filter rows, clean missing data (nulls), and execute group-by aggregations.','📄 Pandas DataFrames Guide','### Data Operations\n```python\nimport pandas as pd\ndf = pd.read_csv(\"students.csv\")\n# Filter rows\nhigh_scorers = df[df[\"score\"] > 80]\n# Group By\nsummary = df.groupby(\"category\")[\"score\"].mean()\nprint(summary)\n```',NULL,'2026-09-27 19:00:19'),(9,3,1,'Introduction to ML Concepts & Workflow','https://www.youtube.com/embed/Gv9_4yMHFhI','11:20','Understand Supervised vs Unsupervised Machine Learning, features, target labels, and train/test splits.','📄 ML Architecture & Terminology Notes','### Key ML Terminology\n- **Supervised Learning**: Training with labeled targets (e.g. Price prediction, Spam detection).\n- **Unsupervised Learning**: Discovering hidden patterns in unlabeled data (e.g. Customer Clustering).\n- **Train/Test Split**: Allocating 80% data for training and 20% for evaluating performance.',NULL,'2026-09-27 19:00:19'),(10,3,2,'Linear Regression & Predictive Modeling','https://www.youtube.com/embed/nk2CQITm_uu','16:45','Implement Simple and Multiple Linear Regression models using Scikit-Learn.','📄 Linear Regression Math & Code Guide','### Scikit-Learn Model Training\n```python\nfrom sklearn.model_selection import train_test_split\nfrom sklearn.linear_model import LinearRegression\n\nX_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2)\nmodel = LinearRegression()\nmodel.fit(X_train, y_train)\npredictions = model.predict(X_test)\n```',NULL,'2026-09-27 19:00:19'),(11,3,3,'Classification Algorithms & Decision Trees','https://www.youtube.com/embed/7VeUPuFGJHk','21:10','Build decision trees, Gini impurity metrics, confusion matrix, accuracy, and F1-score evaluation.','📄 Decision Trees & Classification Manual','### Decision Tree Classifier\n```python\nfrom sklearn.tree import DecisionTreeClassifier\nfrom sklearn.metrics import classification_report\n\nclf = DecisionTreeClassifier(max_depth=5)\nclf.fit(X_train, y_train)\nprint(classification_report(y_test, clf.predict(X_test)))\n```',NULL,'2026-09-27 19:00:19'),(12,4,1,'Foundations of Artificial Intelligence & History','https://www.youtube.com/embed/2ePf9rue1Ao','09:40','Explore the history of AI, Turing test, Narrow AI vs General AI (AGI), and modern applications.','📄 AI Foundations Study Notes','### Overview of AI Branches\n- **Symbolic AI**: Rule-based expert systems.\n- **Machine Learning**: Learning patterns from data.\n- **Deep Learning**: Multi-layer neural network architectures.\n- **Generative AI**: Text and image synthesis (LLMs, Diffusion Models).',NULL,'2026-09-27 19:00:19'),(13,4,2,'Problem Solving & Search Algorithms','https://www.youtube.com/embed/d1K-8F7yDvg','15:00','Study classical search techniques: Breadth-First Search (BFS), Depth-First Search (DFS), and A* Search.','📄 AI Search Algorithms Guide','### Search Strategy Comparison\n- **BFS**: Guarantees shortest path on unweighted graphs. Space complexity O(b^d).\n- **DFS**: Memory efficient but can get trapped in deep branches.\n- **A* Search**: Uses heuristic function f(n) = g(n) + h(n) for optimal pathfinding.',NULL,'2026-09-27 19:00:19'),(14,5,1,'Introduction to Artificial Neural Networks (ANN)','https://www.youtube.com/embed/aircAruvnKk','13:30','Anatomy of an artificial neuron: input features, weights, biases, and summation functions.','📄 Neural Networks Math & Basics','### Mathematical Model of a Neuron\n- Input Vector: X = [x1, x2, ..., xn]\n- Weights: W = [w1, w2, ..., wn]\n- Summation: Z = sum(W * X) + Bias\n- Output: Y = Activation(Z)',NULL,'2026-09-27 19:00:19'),(15,5,2,'Activation Functions & Forward Propagation','https://www.youtube.com/embed/m0pOiSJ1Cdc','16:10','Master ReLU, Sigmoid, Tanh, and Softmax activation functions and multi-layer forward passes.','📄 Activation Functions Reference Card','### Popular Activations\n- **Sigmoid**: Outputs values between 0 and 1.\n- **ReLU**: f(z) = max(0, z). Prevents vanishing gradients.\n- **Softmax**: Converts network logits into multi-class probability distributions.',NULL,'2026-09-27 19:00:19'),(16,6,1,'HTML5 Structure, Forms & Semantic Markup','https://www.youtube.com/embed/pQN-pnXPaVg','14:10','Master modern HTML5 layout tags (`<header>`, `<nav>`, `<main>`, `<section>`), forms, and inputs.','📄 HTML5 Developer Cheatsheet','### Semantic Layout Tags\n```html\n<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n  <title>Full Stack App</title>\n</head>\n<body>\n  <header><h1>Learnora AI</h1></header>\n  <main>\n    <section><h2>Web Dev Lesson</h2></section>\n  </main>\n</body>\n</html>\n```',NULL,'2026-09-27 19:00:19'),(17,6,2,'Responsive Web Design with CSS Flexbox & Grid','https://www.youtube.com/embed/1Rs2ND1ryYc','18:25','Create mobile-friendly web pages using CSS Flexbox layout and 2D CSS Grid systems.','📄 CSS Flexbox & Grid Master Guide','### CSS Flexbox & Grid Syntax\n```css\n.container {\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n}\n.grid-container {\n  display: grid;\n  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));\n  gap: 20px;\n}\n```',NULL,'2026-09-27 19:00:19'),(18,6,3,'Modern JavaScript (ES6+), DOM & Async Events','https://www.youtube.com/embed/hdI2bqOjy3c','23:00','Manipulate HTML elements, handle click events, arrow functions, promises, and async/await API calls.','📄 JavaScript ES6+ Reference Card','### JavaScript Fetch Example\n```javascript\nasync function fetchCourses() {\n  const response = await fetch(\"/api/courses\");\n  const data = await response.json();\n  console.log(data);\n}\n```',NULL,'2026-09-27 19:00:19'),(19,7,1,'HTML Basics: Tags, Headings, Lists & Links','https://www.youtube.com/embed/UB1O30fR-EE','11:00','Learn HTML document structure, tags, headings (`<h1>`-`<h6>`), lists (`<ul>`, `<ol>`), and links (`<a>`).','📄 HTML Core Tags Quick Guide','### Essential HTML Elements\n- `<h1>` to `<h6>`: Headings\n- `<p>`: Paragraph\n- `<a href=\"url\">`: Hyperlink\n- `<img src=\"image.jpg\" alt=\"desc\">`: Image',NULL,'2026-09-27 19:00:19'),(20,7,2,'CSS Styling: Colors, Fonts & Box Model','https://www.youtube.com/embed/yfoY53QXEnI','16:30','Style HTML with CSS properties: color, font-family, margin, padding, border, and background.','📄 CSS Box Model & Color Guide','### CSS Box Model Components\n- Content: Actual element content\n- Padding: Space inside border\n- Border: Outline around element\n- Margin: Space outside border',NULL,'2026-09-27 19:00:19'),(21,8,1,'Java Syntax, Variables & JDK Setup','https://www.youtube.com/embed/eIrMbAQSU34','13:15','Install JDK, set up environment variables, understand JVM vs JRE vs JDK, and run your first Java app.','📄 Java Setup & Hello World Notes','### First Java Class\n```java\npublic class Main {\n    public static void main(String[] args) {\n        System.out.println(\"Hello, Learnora Java Student!\");\n    }\n}\n```\n### Primitive Types\n`int`, `double`, `boolean`, `char`.',NULL,'2026-09-27 19:00:19'),(22,8,2,'Control Statements & Methods in Java','https://www.youtube.com/embed/l9AzO1FMgM8','17:30','Use if-else, switch-case, for/while loops, and static methods for reusable program logic.','📄 Java Control Flow & Methods Reference','### Java Method Declaration\n```java\npublic static int addNumbers(int a, int b) {\n    return a + b;\n}\n```',NULL,'2026-09-27 19:00:19'),(23,9,1,'Database Fundamentals & Relational Data Modeling','https://www.youtube.com/embed/HXV3zeQKqGY','12:00','Introduction to Relational Database Management Systems (RDBMS), tables, primary keys, foreign keys, and SQL basics.','📄 RDBMS & SQL Basics Study Guide','### 1. What is an RDBMS?\nA Relational Database Management System organizes data into tables consisting of rows (records) and columns (attributes).\n\n### 2. Key Concepts\n- **Primary Key**: A unique identifier for every record in a table.\n- **Foreign Key**: A column establishing a link to the primary key of another table.\n- **Normalization**: Organizing database tables to reduce redundancy.\n\n### 3. Basic SQL SELECT Statement\n```sql\nSELECT user_id, name, email FROM users WHERE level = \"Beginner\";\n```',NULL,'2026-09-27 19:00:19'),(24,9,2,'Writing SQL Queries: SELECT, WHERE, ORDER BY','https://www.youtube.com/embed/7S_tz1z_5bA','15:40','Master filtering rows with WHERE conditions, combining logic with AND/OR, sorting results with ORDER BY, and limiting rows.','📄 SQL Querying & Filtering Manual','### Query Syntax Breakdown\n```sql\nSELECT title, category, level, duration \nFROM courses \nWHERE category = \"Programming\" AND level = \"Beginner\" \nORDER BY title ASC \nLIMIT 10;\n```\n\n### Comparison Operators\n- `=`, `<>`, `>`, `<`, `>=`, `<=`\n- `LIKE \"%term%\"`: Pattern matching\n- `IN (\"Val1\", \"Val2\")`: List matching',NULL,'2026-09-27 19:00:19'),(25,10,1,'Introduction to UI & UX Design Principles','https://www.youtube.com/embed/c9Wg6Cb_YlU','10:30','Discover core UX design principles, usability heuristics, accessibility guidelines, and user-centered design.','📄 UI/UX Design Playbook','### 10 Usability Heuristics\n1. Visibility of system status\n2. Match between system and real world\n3. User control and freedom\n4. Consistency and standards\n5. Error prevention',NULL,'2026-09-27 19:00:19'),(26,10,2,'User Research, Personas & User Journey Mapping','https://www.youtube.com/embed/OverJzwft3k','14:20','Conduct user interviews, create target user personas, and map out end-to-end user journeys.','📄 User Research & Journey Mapping Template','### User Journey Mapping Steps\n- Discovery & Goal Definition\n- Touchpoints & User Actions\n- Pain Points & Emotions\n- Opportunity Solutions',NULL,'2026-09-27 19:00:19'),(1621,2,4,'Data Visualization with Matplotlib & Seaborn','https://www.youtube.com/embed/a9UrKTVEeZA','16:20','Create publication-ready line plots, bar charts, histograms, heatmaps, and scatter plots.','📄 Matplotlib & Seaborn Plotting Cheat Sheet','### Plotting Syntax\n```python\nimport matplotlib.pyplot as plt\nimport seaborn as sns\nsns.histplot(df[\"score\"], kde=True)\nplt.title(\"Score Distribution\")\nplt.xlabel(\"Score\")\nplt.show()\n```',NULL,'2026-09-27 19:21:03'),(1625,3,4,'Unsupervised Learning & K-Means Clustering','https://www.youtube.com/embed/4b5d3muPQmA','18:30','Group data points into clusters using K-Means and the Elbow Method.','📄 K-Means Clustering Handbook','### Clustering Example\n```python\nfrom sklearn.cluster import KMeans\nkmeans = KMeans(n_clusters=3)\nkmeans.fit(X)\nprint(\"Cluster Centroids:\", kmeans.cluster_centers_)\n```',NULL,'2026-09-27 19:21:03'),(1628,4,3,'Knowledge Representation & Logic','https://www.youtube.com/embed/Kdf_2qC_sC0','14:15','Represent world knowledge using Propositional Logic, First-Order Logic, and Knowledge Graphs.','📄 Logic Systems & Inference Handbook','### Propositional Logic Syntax\n- Conjunction (AND: ∧), Disjunction (OR: ∨), Implication (IF-THEN: →), Negation (NOT: ¬).\n- Modus Ponens: If P and P → Q, then infer Q.',NULL,'2026-09-27 19:21:03'),(1629,4,4,'AI Ethics, Bias & Responsible AI','https://www.youtube.com/embed/5NgNicCNwE4','12:50','Examine algorithmic bias, privacy, transparency, explainable AI (XAI), and ethical deployment.','📄 Responsible AI Framework Whitepaper','### Core Ethics Principles\n1. Fairness & Non-discrimination\n2. Transparency & Explainability\n3. Privacy & Security\n4. Human Oversight & Accountability',NULL,'2026-09-27 19:21:03'),(1632,5,3,'Backpropagation & Loss Optimization','https://www.youtube.com/embed/IHZwWFHWa-w','17:50','How neural networks learn by calculating partial derivatives and updating weights with Gradient Descent.','📄 Backpropagation & Optimizers Manual','### Optimizers Summary\n- **SGD**: Stochastic Gradient Descent\n- **Adam**: Adaptive Moment Estimation (combines Momentum + RMSProp)',NULL,'2026-09-27 19:21:03'),(1633,5,4,'Convolutional Neural Networks (CNN) for Image Recognition','https://www.youtube.com/embed/YRhxdVk_sIs','22:00','Build CNNs with Convolution layers, Max Pooling, and Flattening for Computer Vision.','📄 CNN Architecture & PyTorch Guide','### CNN Layers\n1. Conv2D: Feature extraction filters\n2. MaxPool2D: Spatial downsampling\n3. Dense/Linear: Classification output',NULL,'2026-09-27 19:21:03'),(1637,6,4,'Backend PHP Development & Database Connectivity','https://www.youtube.com/embed/OK_JCtrrv-c','20:15','Write server-side PHP scripts, execute prepared MySQL queries, manage user sessions and cookies.','📄 PHP & MySQL Backend CRUD Guide','### PHP Prepared Statement\n```php\n$stmt = $conn->prepare(\"SELECT * FROM users WHERE email = ?\");\n$stmt->bind_param(\"s\", $email);\n$stmt->execute();\n$result = $stmt->get_result();\n```',NULL,'2026-09-27 19:21:03'),(1638,6,5,'Building a Full Stack Web Application Capstone','https://www.youtube.com/embed/4W_n5T2666E','26:40','Integrate Frontend UI with Backend PHP & MySQL database into a cohesive web application.','📄 Full Stack Architecture Manual','### Application Architecture\n- Client Layer: HTML5, CSS3, JS\n- Server Layer: Apache + PHP\n- Storage Layer: MySQL database',NULL,'2026-09-27 19:21:03'),(1641,7,3,'Creating Tables, Forms & Inputs in HTML','https://www.youtube.com/embed/KqJikDbsbdU','14:45','Build interactive web forms with inputs, textareas, checkboxes, radio buttons, and submit actions.','📄 HTML Forms Reference Sheet','```html\n<form action=\"submit.php\" method=\"POST\">\n  <label>Email:</label>\n  <input type=\"email\" name=\"user_email\" required>\n  <button type=\"submit\">Submit</button>\n</form>\n```',NULL,'2026-09-27 19:21:03'),(1642,7,4,'Building a Responsive Portfolio Website','https://www.youtube.com/embed/srvUrASNj0s','24:10','Create a complete personal portfolio website from scratch using pure HTML5 and CSS3.','📄 Portfolio Website Step-by-Step Blueprint','### Project Structure\n- `index.html`: Main page layout\n- `style.css`: Theme styling & responsive rules',NULL,'2026-09-27 19:21:03'),(1645,8,3,'Object-Oriented Programming (Classes & Objects)','https://www.youtube.com/embed/bSrm9RXwBaI','20:00','Master four pillars of OOP: Inheritance, Encapsulation, Polymorphism, and Abstraction.','📄 Java OOP Architecture Handbook','### OOP Principles\n- **Encapsulation**: Private fields with getters/setters.\n- **Inheritance**: `class Student extends Person`.\n- **Polymorphism**: Method overriding and overloading.',NULL,'2026-09-27 19:21:03'),(1646,8,4,'Exception Handling & File I/O in Java','https://www.youtube.com/embed/1XAfapkBQjk','16:20','Handle runtime exceptions using try-catch-finally and read/write text files in Java.','📄 Java Exception Handling Guide','```java\ntry {\n    int result = 10 / 0;\n} catch (ArithmeticException e) {\n    System.out.println(\"Cannot divide by zero: \" + e.getMessage());\n}\n```',NULL,'2026-09-27 19:21:03'),(1647,8,5,'Java Collections Framework (ArrayList, HashMap)','https://www.youtube.com/embed/viTHcJM4S-U','19:10','Store and manipulate object groups using ArrayList, HashSet, and HashMap data structures.','📄 Java Collections Framework Manual','```java\nimport java.util.ArrayList;\nArrayList<String> courses = new ArrayList<>();\ncourses.add(\"Java\");\ncourses.add(\"Python\");\n```',NULL,'2026-09-27 19:21:03'),(1650,9,3,'Data Aggregation: GROUP BY, HAVING & Aggregate Functions','https://www.youtube.com/embed/k2kX-5J4iX4','16:15','Summarize dataset stats using COUNT(), SUM(), AVG(), MIN(), MAX() with GROUP BY and HAVING clauses.','📄 SQL Aggregate Functions Cheat Sheet','### Grouping & Aggregation Example\n```sql\nSELECT category, COUNT(*) as total_courses, AVG(progress) as avg_progress\nFROM courses c\nINNER JOIN enrollments e ON c.course_id = e.course_id\nGROUP BY category\nHAVING COUNT(*) > 1;\n```',NULL,'2026-09-27 19:21:03'),(1651,9,4,'Relational Table Joins: INNER, LEFT, RIGHT & FULL JOIN','https://www.youtube.com/embed/9yeOJ0ZMUYw','17:40','Combine data across relational tables using INNER JOIN, LEFT JOIN, and RIGHT JOIN.','📄 SQL Table Joins Visual Handbook','### Join Types Explanation\n- **INNER JOIN**: Returns records that have matching values in both tables.\n- **LEFT JOIN**: Returns all records from left table, and matched records from right table.\n- **RIGHT JOIN**: Returns all records from right table, and matched records from left table.\n\n```sql\nSELECT u.name, c.title, e.progress, e.status\nFROM enrollments e\nINNER JOIN users u ON e.user_id = u.user_id\nINNER JOIN courses c ON e.course_id = c.course_id;\n```',NULL,'2026-09-27 19:21:03'),(1652,9,5,'Database Schema Design & DDL Commands (CREATE, ALTER, DROP)','https://www.youtube.com/embed/QjHSqZpB5bQ','21:10','Design normalized database tables, define column constraints, indexes, and write DDL scripts.','📄 DDL & Database Schema Design Manual','### Table Creation DDL\n```sql\nCREATE TABLE IF NOT EXISTS certificates (\n    certificate_id INT AUTO_INCREMENT PRIMARY KEY,\n    user_id INT NOT NULL,\n    course_id INT NOT NULL,\n    certificate_code VARCHAR(100) NOT NULL UNIQUE,\n    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,\n    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE\n);\n```',NULL,'2026-09-27 19:21:03'),(1655,10,3,'Wireframing & Prototyping in Figma','https://www.youtube.com/embed/FTFaQWZBqQ8','15:45','Create low-fidelity wireframes and high-fidelity interactive web prototypes using Figma.','📄 Figma Shortcuts & Prototyping Guide','### Key Figma Shortcuts\n- `F`: Frame Tool\n- `R`: Rectangle Tool\n- `T`: Text Tool\n- `Shift + A`: Add Auto-Layout',NULL,'2026-09-27 19:21:03'),(1656,10,4,'Visual Hierarchy, Color Theory & Typography','https://www.youtube.com/embed/TR9fWp_2y0E','18:00','Master color contrast ratios, font scaling, grid alignment, and visual emphasis.','📄 Typography & Color Theory Cheat Sheet','### Color Rule 60-30-10\n- 60% Dominant Neutral Color (Background)\n- 30% Secondary Brand Color (Cards/Navigation)\n- 10% Accent Color (Primary Action Buttons)',NULL,'2026-09-27 19:21:03');
/*!40000 ALTER TABLE `lessons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recommendations`
--

DROP TABLE IF EXISTS `recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recommendations` (
  `recommendation_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `similarity_score` decimal(5,4) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`recommendation_id`),
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `recommendations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `recommendations_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recommendations`
--

LOCK TABLES `recommendations` WRITE;
/*!40000 ALTER TABLE `recommendations` DISABLE KEYS */;
/*!40000 ALTER TABLE `recommendations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_activity`
--

DROP TABLE IF EXISTS `user_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_activity` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `activity_type` enum('view','search','like','enroll','complete') DEFAULT NULL,
  `activity_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`activity_id`),
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `user_activity_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `user_activity_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_activity`
--

LOCK TABLES `user_activity` WRITE;
/*!40000 ALTER TABLE `user_activity` DISABLE KEYS */;
INSERT INTO `user_activity` VALUES 
(1,1,1,'view','2026-09-28 01:30:00'),
(2,1,1,'enroll','2026-09-28 01:30:05'),
(3,1,1,'like','2026-09-28 01:30:10'),
(4,1,1,'complete','2026-09-28 01:30:15'),
(5,1,3,'view','2026-09-28 01:30:20'),
(6,1,4,'search','2026-09-28 01:30:25'),
(7,1,11,'view','2026-09-28 01:30:30'),
(8,1,12,'search','2026-09-28 01:30:35');
/*!40000 ALTER TABLE `user_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `education` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `level` enum('Beginner','Intermediate','Advanced') DEFAULT 'Beginner',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'JAGA','test@gmail.com','$2y$10$hCm3PWfBbPAWhjY7nAKSaO1vGVuDx96kSbEqPT2kyGgbilsfbiZp2','MBA','HTML,CSS,','AI MACHINE LEARNING,JAVASCRIPT','Advanced','2026-09-27 18:48:58'),(2,'TEST STUDENTTEST STUDENT','student@example.com','$2y$10$/0h.KLalORDrsZiuK7FvV.2PwxLeTy9YyI4ZLnNf.jU0DemijWvDi','COMPUTER SCIENCE','PYTHON','AIAI','Beginner','2026-09-27 19:05:35');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-28  1:17:25
