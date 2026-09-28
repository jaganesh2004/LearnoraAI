<?php
require_once __DIR__ . "/db.php";

function check_and_init_db($conn) {
    // 1. Create tables
    $sql_lessons = "CREATE TABLE IF NOT EXISTS lessons (
        lesson_id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        lesson_number INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        video_url VARCHAR(500) DEFAULT NULL,
        duration VARCHAR(20) DEFAULT NULL,
        description TEXT DEFAULT NULL,
        document_title VARCHAR(255) DEFAULT NULL,
        document_content TEXT DEFAULT NULL,
        document_url VARCHAR(500) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_course_lesson (course_id, lesson_number),
        CONSTRAINT fk_lessons_course
            FOREIGN KEY (course_id) REFERENCES courses(course_id)
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $sql_progress = "CREATE TABLE IF NOT EXISTS lesson_progress (
        progress_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        lesson_id INT NOT NULL,
        completed TINYINT(1) NOT NULL DEFAULT 0,
        completed_at TIMESTAMP NULL DEFAULT NULL,
        UNIQUE KEY unique_user_lesson (user_id, lesson_id),
        CONSTRAINT fk_progress_user
            FOREIGN KEY (user_id) REFERENCES users(user_id)
            ON DELETE CASCADE,
        CONSTRAINT fk_progress_lesson
            FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id)
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $sql_certs = "CREATE TABLE IF NOT EXISTS certificates (
        certificate_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        course_id INT NOT NULL,
        certificate_code VARCHAR(100) NOT NULL UNIQUE,
        issued_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_course_certificate (user_id, course_id),
        CONSTRAINT fk_certificate_user
            FOREIGN KEY (user_id) REFERENCES users(user_id)
            ON DELETE CASCADE,
        CONSTRAINT fk_certificate_course
            FOREIGN KEY (course_id) REFERENCES courses(course_id)
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $conn->query($sql_lessons);
    $conn->query($sql_progress);
    $conn->query($sql_certs);

    // Ensure default catalog of courses exists
    $default_courses = [
        ["title" => "Python Programming for Beginners", "category" => "Programming", "description" => "Learn Python programming from basics.", "skills" => "Python, Programming", "level" => "Beginner", "duration" => "6 Weeks", "instructor" => "Arun Kumar"],
        ["title" => "Python for Data Science", "category" => "Data Science", "description" => "Learn Python, Pandas and NumPy for data science.", "skills" => "Python, Pandas, NumPy, Data Science", "level" => "Beginner", "duration" => "8 Weeks", "instructor" => "Dr. Meena"],
        ["title" => "Machine Learning Fundamentals", "category" => "AI & ML", "description" => "Learn the basics of Machine Learning algorithms.", "skills" => "Python, Machine Learning, Scikit-learn", "level" => "Intermediate", "duration" => "10 Weeks", "instructor" => "Karthik Raj"],
        ["title" => "Artificial Intelligence Basics", "category" => "AI & ML", "description" => "Learn the fundamentals of Artificial Intelligence.", "skills" => "AI, Python, Machine Learning", "level" => "Beginner", "duration" => "6 Weeks", "instructor" => "Dr. Anitha"],
        ["title" => "Deep Learning with Python", "category" => "AI & ML", "description" => "Learn neural networks and deep learning.", "skills" => "Python, Deep Learning, Neural Networks", "level" => "Advanced", "duration" => "12 Weeks", "instructor" => "Vijay Kumar"],
        ["title" => "Full Stack Web Development", "category" => "Web Development", "description" => "Learn frontend and backend web development.", "skills" => "HTML, CSS, JavaScript, PHP, MySQL", "level" => "Intermediate", "duration" => "12 Weeks", "instructor" => "Sanjay Kumar"],
        ["title" => "HTML and CSS for Beginners", "category" => "Web Development", "description" => "Learn how to create modern websites.", "skills" => "HTML, CSS, Web Design", "level" => "Beginner", "duration" => "4 Weeks", "instructor" => "Divya"],
        ["title" => "Java Programming", "category" => "Programming", "description" => "Learn Java and object-oriented programming.", "skills" => "Java, OOP, Programming", "level" => "Intermediate", "duration" => "8 Weeks", "instructor" => "Rajesh"],
        ["title" => "SQL and Database Management", "category" => "Database", "description" => "Learn SQL and MySQL database management.", "skills" => "SQL, MySQL, Database", "level" => "Beginner", "duration" => "5 Weeks", "instructor" => "Manoj"],
        ["title" => "UI UX Design Fundamentals", "category" => "Design", "description" => "Learn basic UI and UX design concepts.", "skills" => "UI Design, UX, Figma", "level" => "Beginner", "duration" => "6 Weeks", "instructor" => "Keerthi"],
        ["title" => "Modern JavaScript Essentials", "category" => "Web Development", "description" => "Master core JavaScript (ES6+), DOM manipulation, async/await, and modern JS patterns.", "skills" => "JavaScript, JS, ES6, DOM, Async JS", "level" => "Beginner", "duration" => "6 Weeks", "instructor" => "Arun Kumar"],
        ["title" => "React JS Frontend Development", "category" => "Web Development", "description" => "Build dynamic, interactive user interfaces with React JS, Hooks, State Management, and Redux Toolkit.", "skills" => "React, React JS, JavaScript, Hooks, Frontend", "level" => "Intermediate", "duration" => "8 Weeks", "instructor" => "Divya"],
        ["title" => "PHP & Laravel Web Framework", "category" => "Web Development", "description" => "Learn modern backend web development with PHP 8, Laravel Framework, MVC architecture, and REST APIs.", "skills" => "PHP, Laravel, Backend, MySQL, REST API", "level" => "Intermediate", "duration" => "8 Weeks", "instructor" => "Sanjay Kumar"],
        ["title" => "Cyber Security & Ethical Hacking", "category" => "Cyber Security", "description" => "Master network security, ethical hacking, vulnerability scanning, penetration testing, and web app security.", "skills" => "Cyber Security, Ethical Hacking, Network Security, Pen Testing", "level" => "Beginner", "duration" => "8 Weeks", "instructor" => "Karthik Raj"],
        ["title" => "Network Security & Cryptography", "category" => "Cyber Security", "description" => "Explore data encryption, Public Key Infrastructure (PKI), SSL/TLS protocols, firewalls, and cyber defense strategy.", "skills" => "Cyber Security, Cryptography, Network Security, SSL, Firewalls", "level" => "Advanced", "duration" => "10 Weeks", "instructor" => "Dr. Anitha"],
        ["title" => "Full-Stack React & Next.js", "category" => "Web Development", "description" => "Build server-rendered, high performance web apps using Next.js, Server Components, React 19, and Tailwind CSS.", "skills" => "React, Next.js, Full-Stack, Web Development, JavaScript", "level" => "Advanced", "duration" => "10 Weeks", "instructor" => "Divya"],
        ["title" => "Cloud Computing & AWS Essentials", "category" => "Cloud & DevOps", "description" => "Learn Cloud Architecture, Amazon Web Services (AWS), EC2, S3, Lambda, and cloud deployment pipelines.", "skills" => "Cloud Computing, AWS, DevOps, Cloud Security, System Architecture", "level" => "Intermediate", "duration" => "8 Weeks", "instructor" => "Manoj"],
        ["title" => "Data Structures & Algorithms in JS", "category" => "Computer Science", "description" => "Master arrays, linked lists, trees, graphs, sorting algorithms, and dynamic programming in JavaScript.", "skills" => "JavaScript, Data Structures, Algorithms, Problem Solving", "level" => "Intermediate", "duration" => "8 Weeks", "instructor" => "Dr. Meena"]
    ];

    $c_stmt = $conn->prepare("INSERT INTO courses (title, category, description, skills, level, duration, instructor) SELECT ?, ?, ?, ?, ?, ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM courses WHERE title = ?)");
    foreach ($default_courses as $dc) {
        $c_stmt->bind_param("ssssssss", $dc["title"], $dc["category"], $dc["description"], $dc["skills"], $dc["level"], $dc["duration"], $dc["instructor"], $dc["title"]);
        $c_stmt->execute();
    }
    $c_stmt->close();

    // Fetch all courses
    $courses_res = $conn->query("SELECT course_id, title FROM courses");
    if (!$courses_res || $courses_res->num_rows === 0) {
        return;
    }

    $all_courses = [];
    while ($r = $courses_res->fetch_assoc()) {
        $all_courses[$r["course_id"]] = $r["title"];
    }

    // Seed data template dictionary
    $seed_data = [
        "Python Programming for Beginners" => [
            [
                "num" => 1,
                "title" => "Introduction to Python & Environment Setup",
                "video" => "https://www.youtube.com/embed/rfscVS0vtbw",
                "duration" => "08:30",
                "desc" => "Overview of Python programming language, installation of Python 3, setting up VS Code, and running your first Hello World script.",
                "doc_title" => "📄 Python Setup & Hello World Study Notes",
                "doc_content" => '### 1. What is Python?
Python is a high-level, interpreted language created by Guido van Rossum. It emphasizes code readability with clean, syntax-driven blocks.

### 2. Installing Python & VS Code
- Download Python 3.x from python.org.
- Ensure "Add Python to PATH" is checked during installation.
- Install VS Code and the Python Extension.

### 3. Writing Your First Code
```python
# First Python Script
print("Hello, Learnora AI Student!")
name = input("Enter your name: ")
print(f"Welcome to Python, {name}!")
```

### Key Concepts:
- Case-sensitivity: `name` and `Name` are distinct variables.
- Comments begin with `#` symbol.'
            ],
            [
                "num" => 2,
                "title" => "Variables, Data Types & Basic Operators",
                "video" => "https://www.youtube.com/embed/kqtD5dpn9C8",
                "duration" => "12:45",
                "desc" => "Master fundamental Python data types: Integers, Floats, Strings, Booleans, Lists, and Dictionaries alongside arithmetic operators.",
                "doc_title" => "📄 Data Types & Operators Cheat Sheet",
                "doc_content" => '### Primitive Data Types
- **int**: Whole numbers e.g. `count = 42`
- **float**: Decimals e.g. `price = 19.99`
- **str**: Text enclosed in quotes e.g. `course = "Python"`
- **bool**: Boolean truth values `is_active = True`

### Data Structures
```python
# List (Ordered & Mutable)
skills = ["Python", "SQL", "JavaScript"]
skills.append("Git")

# Dictionary (Key-Value Pairs)
student = {"name": "Alex", "grade": 95, "passed": True}
print(student["name"])
```

### Arithmetic Operators
- Addition (`+`), Subtraction (`-`), Multiplication (`*`), Division (`/`), Floor Division (`//`), Modulo (`%`).'
            ],
            [
                "num" => 3,
                "title" => "Control Flow: If-Else Conditions & Loops",
                "video" => "https://www.youtube.com/embed/6iF8Xb7Z3wQ",
                "duration" => "15:20",
                "desc" => "Learn how to make decisions using if-elif-else statements and execute repetitive tasks with for and while loops.",
                "doc_title" => "📄 Conditions & Loops Reference Guide",
                "doc_content" => '### Conditional Logic
```python
score = 88
if score >= 90:
    print("Grade: A")
elif score >= 80:
    print("Grade: B")
else:
    print("Grade: C or below")
```

### Iteration with For & While Loops
```python
# For Loop with range
for i in range(1, 6):
    print(f"Iteration #{i}")

# While Loop
count = 3
while count > 0:
    print(f"Countdown: {count}")
    count -= 1
```'
            ],
            [
                "num" => 4,
                "title" => "Python Functions & Scope",
                "video" => "https://www.youtube.com/embed/9Os0o3wzS_I",
                "duration" => "18:10",
                "desc" => "Create modular reusable code using def functions, parameters, return values, and scope management.",
                "doc_title" => "📄 Functions & Scope Manual",
                "doc_content" => '### Defining Functions
```python
def calculate_total(price, tax_rate=0.05):
    """Calculates final price with tax."""
    total = price * (1 + tax_rate)
    return round(total, 2)

# Function Calls
print(calculate_total(100))
print(calculate_total(200, 0.08))
```

### Key Principles
- DRY (Don\'t Repeat Yourself)
- Global vs Local Variable Scope'
            ],
            [
                "num" => 5,
                "title" => "Building a Python Command Line Mini Project",
                "video" => "https://www.youtube.com/embed/8ext9G7xspg",
                "duration" => "22:30",
                "desc" => "Apply your skills to build a real-world CLI Student Grade & Course Management Application.",
                "doc_title" => "📄 Capstone Project Guide & Code",
                "doc_content" => '### Student Management System Project
Build a CLI app that prompts users to add students, enter scores, calculate class averages, and print top performers.'
            ]
        ],
        "Python for Data Science" => [
            [
                "num" => 1,
                "title" => "Data Science Overview & Jupyter Notebooks Setup",
                "video" => "https://www.youtube.com/embed/LHBE6Q9XlzI",
                "duration" => "10:15",
                "desc" => "Understand the Data Science lifecycle, set up Anaconda distribution and launch Jupyter Notebooks.",
                "doc_title" => "📄 Data Science Environment Setup",
                "doc_content" => '### Data Science Stack
- Jupyter Lab / Notebooks
- NumPy: Numerical matrix computing
- Pandas: Dataframes & CSV processing
- Matplotlib / Seaborn: Visualization

### Jupyter Commands
- Shift + Enter: Run cell and select below
- Esc + M: Convert cell to Markdown
- Esc + A: Insert cell above'
            ],
            [
                "num" => 2,
                "title" => "NumPy Arrays & Vectorized Computations",
                "video" => "https://www.youtube.com/embed/QUT1VHiLmmI",
                "duration" => "14:50",
                "desc" => "Perform high-performance N-dimensional array manipulations, slicing, broadcasting, and matrix math.",
                "doc_title" => "📄 NumPy Matrix Reference Sheet",
                "doc_content" => '### Creating Arrays
```python
import numpy as np
a = np.array([1, 2, 3, 4])
b = np.zeros((3, 3))
c = np.arange(0, 10, 2)
```
### Vectorized Operations
```python
values = np.array([10, 20, 30])
result = values * 2 + 5
print("Mean:", np.mean(result))
```'
            ],
            [
                "num" => 3,
                "title" => "Data Wrangling & Analysis with Pandas",
                "video" => "https://www.youtube.com/embed/vmEHCJofslg",
                "duration" => "19:40",
                "desc" => "Load CSV datasets, filter rows, clean missing data (nulls), and execute group-by aggregations.",
                "doc_title" => "📄 Pandas DataFrames Guide",
                "doc_content" => '### Data Operations
```python
import pandas as pd
df = pd.read_csv("students.csv")
# Filter rows
high_scorers = df[df["score"] > 80]
# Group By
summary = df.groupby("category")["score"].mean()
print(summary)
```'
            ],
            [
                "num" => 4,
                "title" => "Data Visualization with Matplotlib & Seaborn",
                "video" => "https://www.youtube.com/embed/a9UrKTVEeZA",
                "duration" => "16:20",
                "desc" => "Create publication-ready line plots, bar charts, histograms, heatmaps, and scatter plots.",
                "doc_title" => "📄 Matplotlib & Seaborn Plotting Cheat Sheet",
                "doc_content" => '### Plotting Syntax
```python
import matplotlib.pyplot as plt
import seaborn as sns
sns.histplot(df["score"], kde=True)
plt.title("Score Distribution")
plt.xlabel("Score")
plt.show()
```'
            ]
        ],
        "Machine Learning Fundamentals" => [
            [
                "num" => 1,
                "title" => "Introduction to ML Concepts & Workflow",
                "video" => "https://www.youtube.com/embed/Gv9_4yMHFhI",
                "duration" => "11:20",
                "desc" => "Understand Supervised vs Unsupervised Machine Learning, features, target labels, and train/test splits.",
                "doc_title" => "📄 ML Architecture & Terminology Notes",
                "doc_content" => '### Key ML Terminology
- **Supervised Learning**: Training with labeled targets (e.g. Price prediction, Spam detection).
- **Unsupervised Learning**: Discovering hidden patterns in unlabeled data (e.g. Customer Clustering).
- **Train/Test Split**: Allocating 80% data for training and 20% for evaluating performance.'
            ],
            [
                "num" => 2,
                "title" => "Linear Regression & Predictive Modeling",
                "video" => "https://www.youtube.com/embed/nk2CQITm_uu",
                "duration" => "16:45",
                "desc" => "Implement Simple and Multiple Linear Regression models using Scikit-Learn.",
                "doc_title" => "📄 Linear Regression Math & Code Guide",
                "doc_content" => '### Scikit-Learn Model Training
```python
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2)
model = LinearRegression()
model.fit(X_train, y_train)
predictions = model.predict(X_test)
```'
            ],
            [
                "num" => 3,
                "title" => "Classification Algorithms & Decision Trees",
                "video" => "https://www.youtube.com/embed/7VeUPuFGJHk",
                "duration" => "21:10",
                "desc" => "Build decision trees, Gini impurity metrics, confusion matrix, accuracy, and F1-score evaluation.",
                "doc_title" => "📄 Decision Trees & Classification Manual",
                "doc_content" => '### Decision Tree Classifier
```python
from sklearn.tree import DecisionTreeClassifier
from sklearn.metrics import classification_report

clf = DecisionTreeClassifier(max_depth=5)
clf.fit(X_train, y_train)
print(classification_report(y_test, clf.predict(X_test)))
```'
            ],
            [
                "num" => 4,
                "title" => "Unsupervised Learning & K-Means Clustering",
                "video" => "https://www.youtube.com/embed/4b5d3muPQmA",
                "duration" => "18:30",
                "desc" => "Group data points into clusters using K-Means and the Elbow Method.",
                "doc_title" => "📄 K-Means Clustering Handbook",
                "doc_content" => '### Clustering Example
```python
from sklearn.cluster import KMeans
kmeans = KMeans(n_clusters=3)
kmeans.fit(X)
print("Cluster Centroids:", kmeans.cluster_centers_)
```'
            ]
        ],
        "Artificial Intelligence Basics" => [
            [
                "num" => 1,
                "title" => "Foundations of Artificial Intelligence & History",
                "video" => "https://www.youtube.com/embed/2ePf9rue1Ao",
                "duration" => "09:40",
                "desc" => "Explore the history of AI, Turing test, Narrow AI vs General AI (AGI), and modern applications.",
                "doc_title" => "📄 AI Foundations Study Notes",
                "doc_content" => '### Overview of AI Branches
- **Symbolic AI**: Rule-based expert systems.
- **Machine Learning**: Learning patterns from data.
- **Deep Learning**: Multi-layer neural network architectures.
- **Generative AI**: Text and image synthesis (LLMs, Diffusion Models).'
            ],
            [
                "num" => 2,
                "title" => "Problem Solving & Search Algorithms",
                "video" => "https://www.youtube.com/embed/d1K-8F7yDvg",
                "duration" => "15:00",
                "desc" => "Study classical search techniques: Breadth-First Search (BFS), Depth-First Search (DFS), and A* Search.",
                "doc_title" => "📄 AI Search Algorithms Guide",
                "doc_content" => '### Search Strategy Comparison
- **BFS**: Guarantees shortest path on unweighted graphs. Space complexity O(b^d).
- **DFS**: Memory efficient but can get trapped in deep branches.
- **A* Search**: Uses heuristic function f(n) = g(n) + h(n) for optimal pathfinding.'
            ],
            [
                "num" => 3,
                "title" => "Knowledge Representation & Logic",
                "video" => "https://www.youtube.com/embed/Kdf_2qC_sC0",
                "duration" => "14:15",
                "desc" => "Represent world knowledge using Propositional Logic, First-Order Logic, and Knowledge Graphs.",
                "doc_title" => "📄 Logic Systems & Inference Handbook",
                "doc_content" => '### Propositional Logic Syntax
- Conjunction (AND: ∧), Disjunction (OR: ∨), Implication (IF-THEN: →), Negation (NOT: ¬).
- Modus Ponens: If P and P → Q, then infer Q.'
            ],
            [
                "num" => 4,
                "title" => "AI Ethics, Bias & Responsible AI",
                "video" => "https://www.youtube.com/embed/5NgNicCNwE4",
                "duration" => "12:50",
                "desc" => "Examine algorithmic bias, privacy, transparency, explainable AI (XAI), and ethical deployment.",
                "doc_title" => "📄 Responsible AI Framework Whitepaper",
                "doc_content" => '### Core Ethics Principles
1. Fairness & Non-discrimination
2. Transparency & Explainability
3. Privacy & Security
4. Human Oversight & Accountability'
            ]
        ],
        "Deep Learning with Python" => [
            [
                "num" => 1,
                "title" => "Introduction to Artificial Neural Networks (ANN)",
                "video" => "https://www.youtube.com/embed/aircAruvnKk",
                "duration" => "13:30",
                "desc" => "Anatomy of an artificial neuron: input features, weights, biases, and summation functions.",
                "doc_title" => "📄 Neural Networks Math & Basics",
                "doc_content" => '### Mathematical Model of a Neuron
- Input Vector: X = [x1, x2, ..., xn]
- Weights: W = [w1, w2, ..., wn]
- Summation: Z = sum(W * X) + Bias
- Output: Y = Activation(Z)'
            ],
            [
                "num" => 2,
                "title" => "Activation Functions & Forward Propagation",
                "video" => "https://www.youtube.com/embed/m0pOiSJ1Cdc",
                "duration" => "16:10",
                "desc" => "Master ReLU, Sigmoid, Tanh, and Softmax activation functions and multi-layer forward passes.",
                "doc_title" => "📄 Activation Functions Reference Card",
                "doc_content" => '### Popular Activations
- **Sigmoid**: Outputs values between 0 and 1.
- **ReLU**: f(z) = max(0, z). Prevents vanishing gradients.
- **Softmax**: Converts network logits into multi-class probability distributions.'
            ],
            [
                "num" => 3,
                "title" => "Backpropagation & Loss Optimization",
                "video" => "https://www.youtube.com/embed/IHZwWFHWa-w",
                "duration" => "17:50",
                "desc" => "How neural networks learn by calculating partial derivatives and updating weights with Gradient Descent.",
                "doc_title" => "📄 Backpropagation & Optimizers Manual",
                "doc_content" => '### Optimizers Summary
- **SGD**: Stochastic Gradient Descent
- **Adam**: Adaptive Moment Estimation (combines Momentum + RMSProp)'
            ],
            [
                "num" => 4,
                "title" => "Convolutional Neural Networks (CNN) for Image Recognition",
                "video" => "https://www.youtube.com/embed/YRhxdVk_sIs",
                "duration" => "22:00",
                "desc" => "Build CNNs with Convolution layers, Max Pooling, and Flattening for Computer Vision.",
                "doc_title" => "📄 CNN Architecture & PyTorch Guide",
                "doc_content" => '### CNN Layers
1. Conv2D: Feature extraction filters
2. MaxPool2D: Spatial downsampling
3. Dense/Linear: Classification output'
            ]
        ],
        "Full Stack Web Development" => [
            [
                "num" => 1,
                "title" => "HTML5 Structure, Forms & Semantic Markup",
                "video" => "https://www.youtube.com/embed/pQN-pnXPaVg",
                "duration" => "14:10",
                "desc" => "Master modern HTML5 layout tags (`<header>`, `<nav>`, `<main>`, `<section>`), forms, and inputs.",
                "doc_title" => "📄 HTML5 Developer Cheatsheet",
                "doc_content" => '### Semantic Layout Tags
```html
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Full Stack App</title>
</head>
<body>
  <header><h1>Learnora AI</h1></header>
  <main>
    <section><h2>Web Dev Lesson</h2></section>
  </main>
</body>
</html>
```'
            ],
            [
                "num" => 2,
                "title" => "Responsive Web Design with CSS Flexbox & Grid",
                "video" => "https://www.youtube.com/embed/1Rs2ND1ryYc",
                "duration" => "18:25",
                "desc" => "Create mobile-friendly web pages using CSS Flexbox layout and 2D CSS Grid systems.",
                "doc_title" => "📄 CSS Flexbox & Grid Master Guide",
                "doc_content" => '### CSS Flexbox & Grid Syntax
```css
.container {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.grid-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}
```'
            ],
            [
                "num" => 3,
                "title" => "Modern JavaScript (ES6+), DOM & Async Events",
                "video" => "https://www.youtube.com/embed/hdI2bqOjy3c",
                "duration" => "23:00",
                "desc" => "Manipulate HTML elements, handle click events, arrow functions, promises, and async/await API calls.",
                "doc_title" => "📄 JavaScript ES6+ Reference Card",
                "doc_content" => '### JavaScript Fetch Example
```javascript
async function fetchCourses() {
  const response = await fetch("/api/courses");
  const data = await response.json();
  console.log(data);
}
```'
            ],
            [
                "num" => 4,
                "title" => "Backend PHP Development & Database Connectivity",
                "video" => "https://www.youtube.com/embed/OK_JCtrrv-c",
                "duration" => "20:15",
                "desc" => "Write server-side PHP scripts, execute prepared MySQL queries, manage user sessions and cookies.",
                "doc_title" => "📄 PHP & MySQL Backend CRUD Guide",
                "doc_content" => '### PHP Prepared Statement
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
```'
            ],
            [
                "num" => 5,
                "title" => "Building a Full Stack Web Application Capstone",
                "video" => "https://www.youtube.com/embed/4W_n5T2666E",
                "duration" => "26:40",
                "desc" => "Integrate Frontend UI with Backend PHP & MySQL database into a cohesive web application.",
                "doc_title" => "📄 Full Stack Architecture Manual",
                "doc_content" => '### Application Architecture
- Client Layer: HTML5, CSS3, JS
- Server Layer: Apache + PHP
- Storage Layer: MySQL database'
            ]
        ],
        "HTML and CSS for Beginners" => [
            [
                "num" => 1,
                "title" => "HTML Basics: Tags, Headings, Lists & Links",
                "video" => "https://www.youtube.com/embed/UB1O30fR-EE",
                "duration" => "11:00",
                "desc" => "Learn HTML document structure, tags, headings (`<h1>`-`<h6>`), lists (`<ul>`, `<ol>`), and links (`<a>`).",
                "doc_title" => "📄 HTML Core Tags Quick Guide",
                "doc_content" => '### Essential HTML Elements
- `<h1>` to `<h6>`: Headings
- `<p>`: Paragraph
- `<a href="url">`: Hyperlink
- `<img src="image.jpg" alt="desc">`: Image'
            ],
            [
                "num" => 2,
                "title" => "CSS Styling: Colors, Fonts & Box Model",
                "video" => "https://www.youtube.com/embed/yfoY53QXEnI",
                "duration" => "16:30",
                "desc" => "Style HTML with CSS properties: color, font-family, margin, padding, border, and background.",
                "doc_title" => "📄 CSS Box Model & Color Guide",
                "doc_content" => '### CSS Box Model Components
- Content: Actual element content
- Padding: Space inside border
- Border: Outline around element
- Margin: Space outside border'
            ],
            [
                "num" => 3,
                "title" => "Creating Tables, Forms & Inputs in HTML",
                "video" => "https://www.youtube.com/embed/KqJikDbsbdU",
                "duration" => "14:45",
                "desc" => "Build interactive web forms with inputs, textareas, checkboxes, radio buttons, and submit actions.",
                "doc_title" => "📄 HTML Forms Reference Sheet",
                "doc_content" => '```html
<form action="submit.php" method="POST">
  <label>Email:</label>
  <input type="email" name="user_email" required>
  <button type="submit">Submit</button>
</form>
```'
            ],
            [
                "num" => 4,
                "title" => "Building a Responsive Portfolio Website",
                "video" => "https://www.youtube.com/embed/srvUrASNj0s",
                "duration" => "24:10",
                "desc" => "Create a complete personal portfolio website from scratch using pure HTML5 and CSS3.",
                "doc_title" => "📄 Portfolio Website Step-by-Step Blueprint",
                "doc_content" => '### Project Structure
- `index.html`: Main page layout
- `style.css`: Theme styling & responsive rules'
            ]
        ],
        "Java Programming" => [
            [
                "num" => 1,
                "title" => "Java Syntax, Variables & JDK Setup",
                "video" => "https://www.youtube.com/embed/eIrMbAQSU34",
                "duration" => "13:15",
                "desc" => "Install JDK, set up environment variables, understand JVM vs JRE vs JDK, and run your first Java app.",
                "doc_title" => "📄 Java Setup & Hello World Notes",
                "doc_content" => '### First Java Class
```java
public class Main {
    public static void main(String[] args) {
        System.out.println("Hello, Learnora Java Student!");
    }
}
```
### Primitive Types
`int`, `double`, `boolean`, `char`.'
            ],
            [
                "num" => 2,
                "title" => "Control Statements & Methods in Java",
                "video" => "https://www.youtube.com/embed/l9AzO1FMgM8",
                "duration" => "17:30",
                "desc" => "Use if-else, switch-case, for/while loops, and static methods for reusable program logic.",
                "doc_title" => "📄 Java Control Flow & Methods Reference",
                "doc_content" => '### Java Method Declaration
```java
public static int addNumbers(int a, int b) {
    return a + b;
}
```'
            ],
            [
                "num" => 3,
                "title" => "Object-Oriented Programming (Classes & Objects)",
                "video" => "https://www.youtube.com/embed/bSrm9RXwBaI",
                "duration" => "20:00",
                "desc" => "Master four pillars of OOP: Inheritance, Encapsulation, Polymorphism, and Abstraction.",
                "doc_title" => "📄 Java OOP Architecture Handbook",
                "doc_content" => '### OOP Principles
- **Encapsulation**: Private fields with getters/setters.
- **Inheritance**: `class Student extends Person`.
- **Polymorphism**: Method overriding and overloading.'
            ],
            [
                "num" => 4,
                "title" => "Exception Handling & File I/O in Java",
                "video" => "https://www.youtube.com/embed/1XAfapkBQjk",
                "duration" => "16:20",
                "desc" => "Handle runtime exceptions using try-catch-finally and read/write text files in Java.",
                "doc_title" => "📄 Java Exception Handling Guide",
                "doc_content" => '```java
try {
    int result = 10 / 0;
} catch (ArithmeticException e) {
    System.out.println("Cannot divide by zero: " + e.getMessage());
}
```'
            ],
            [
                "num" => 5,
                "title" => "Java Collections Framework (ArrayList, HashMap)",
                "video" => "https://www.youtube.com/embed/viTHcJM4S-U",
                "duration" => "19:10",
                "desc" => "Store and manipulate object groups using ArrayList, HashSet, and HashMap data structures.",
                "doc_title" => "📄 Java Collections Framework Manual",
                "doc_content" => '```java
import java.util.ArrayList;
ArrayList<String> courses = new ArrayList<>();
courses.add("Java");
courses.add("Python");
```'
            ]
        ],
        "SQL and Database Management" => [
            [
                "num" => 1,
                "title" => "Database Fundamentals & Relational Data Modeling",
                "video" => "https://www.youtube.com/embed/HXV3zeQKqGY",
                "duration" => "12:00",
                "desc" => "Introduction to Relational Database Management Systems (RDBMS), tables, primary keys, foreign keys, and SQL basics.",
                "doc_title" => "📄 RDBMS & SQL Basics Study Guide",
                "doc_content" => '### 1. What is an RDBMS?
A Relational Database Management System organizes data into tables consisting of rows (records) and columns (attributes).

### 2. Key Concepts
- **Primary Key**: A unique identifier for every record in a table.
- **Foreign Key**: A column establishing a link to the primary key of another table.
- **Normalization**: Organizing database tables to reduce redundancy.

### 3. Basic SQL SELECT Statement
```sql
SELECT user_id, name, email FROM users WHERE level = "Beginner";
```'
            ],
            [
                "num" => 2,
                "title" => "Writing SQL Queries: SELECT, WHERE, ORDER BY",
                "video" => "https://www.youtube.com/embed/7S_tz1z_5bA",
                "duration" => "15:40",
                "desc" => "Master filtering rows with WHERE conditions, combining logic with AND/OR, sorting results with ORDER BY, and limiting rows.",
                "doc_title" => "📄 SQL Querying & Filtering Manual",
                "doc_content" => '### Query Syntax Breakdown
```sql
SELECT title, category, level, duration 
FROM courses 
WHERE category = "Programming" AND level = "Beginner" 
ORDER BY title ASC 
LIMIT 10;
```

### Comparison Operators
- `=`, `<>`, `>`, `<`, `>=`, `<=`
- `LIKE "%term%"`: Pattern matching
- `IN ("Val1", "Val2")`: List matching'
            ],
            [
                "num" => 3,
                "title" => "Data Aggregation: GROUP BY, HAVING & Aggregate Functions",
                "video" => "https://www.youtube.com/embed/k2kX-5J4iX4",
                "duration" => "16:15",
                "desc" => "Summarize dataset stats using COUNT(), SUM(), AVG(), MIN(), MAX() with GROUP BY and HAVING clauses.",
                "doc_title" => "📄 SQL Aggregate Functions Cheat Sheet",
                "doc_content" => '### Grouping & Aggregation Example
```sql
SELECT category, COUNT(*) as total_courses, AVG(progress) as avg_progress
FROM courses c
INNER JOIN enrollments e ON c.course_id = e.course_id
GROUP BY category
HAVING COUNT(*) > 1;
```'
            ],
            [
                "num" => 4,
                "title" => "Relational Table Joins: INNER, LEFT, RIGHT & FULL JOIN",
                "video" => "https://www.youtube.com/embed/9yeOJ0ZMUYw",
                "duration" => "17:40",
                "desc" => "Combine data across relational tables using INNER JOIN, LEFT JOIN, and RIGHT JOIN.",
                "doc_title" => "📄 SQL Table Joins Visual Handbook",
                "doc_content" => '### Join Types Explanation
- **INNER JOIN**: Returns records that have matching values in both tables.
- **LEFT JOIN**: Returns all records from left table, and matched records from right table.
- **RIGHT JOIN**: Returns all records from right table, and matched records from left table.

```sql
SELECT u.name, c.title, e.progress, e.status
FROM enrollments e
INNER JOIN users u ON e.user_id = u.user_id
INNER JOIN courses c ON e.course_id = c.course_id;
```'
            ],
            [
                "num" => 5,
                "title" => "Database Schema Design & DDL Commands (CREATE, ALTER, DROP)",
                "video" => "https://www.youtube.com/embed/QjHSqZpB5bQ",
                "duration" => "21:10",
                "desc" => "Design normalized database tables, define column constraints, indexes, and write DDL scripts.",
                "doc_title" => "📄 DDL & Database Schema Design Manual",
                "doc_content" => '### Table Creation DDL
```sql
CREATE TABLE IF NOT EXISTS certificates (
    certificate_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    certificate_code VARCHAR(100) NOT NULL UNIQUE,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);
```'
            ]
        ],
        "UI UX Design Fundamentals" => [
            [
                "num" => 1,
                "title" => "Introduction to UI & UX Design Principles",
                "video" => "https://www.youtube.com/embed/c9Wg6Cb_YlU",
                "duration" => "10:30",
                "desc" => "Discover core UX design principles, usability heuristics, accessibility guidelines, and user-centered design.",
                "doc_title" => "📄 UI/UX Design Playbook",
                "doc_content" => '### 10 Usability Heuristics
1. Visibility of system status
2. Match between system and real world
3. User control and freedom
4. Consistency and standards
5. Error prevention'
            ],
            [
                "num" => 2,
                "title" => "User Research, Personas & User Journey Mapping",
                "video" => "https://www.youtube.com/embed/OverJzwft3k",
                "duration" => "14:20",
                "desc" => "Conduct user interviews, create target user personas, and map out end-to-end user journeys.",
                "doc_title" => "📄 User Research & Journey Mapping Template",
                "doc_content" => '### User Journey Mapping Steps
- Discovery & Goal Definition
- Touchpoints & User Actions
- Pain Points & Emotions
- Opportunity Solutions'
            ],
            [
                "num" => 3,
                "title" => "Wireframing & Prototyping in Figma",
                "video" => "https://www.youtube.com/embed/FTFaQWZBqQ8",
                "duration" => "15:45",
                "desc" => "Create low-fidelity wireframes and high-fidelity interactive web prototypes using Figma.",
                "doc_title" => "📄 Figma Shortcuts & Prototyping Guide",
                "doc_content" => '### Key Figma Shortcuts
- `F`: Frame Tool
- `R`: Rectangle Tool
- `T`: Text Tool
- `Shift + A`: Add Auto-Layout'
            ],
            [
                "num" => 4,
                "title" => "Visual Hierarchy, Color Theory & Typography",
                "video" => "https://www.youtube.com/embed/TR9fWp_2y0E",
                "duration" => "18:00",
                "desc" => "Master color contrast ratios, font scaling, grid alignment, and visual emphasis.",
                "doc_title" => "📄 Typography & Color Theory Cheat Sheet",
                "doc_content" => '### Color Rule 60-30-10
- 60% Dominant Neutral Color (Background)
- 30% Secondary Brand Color (Cards/Navigation)
- 10% Accent Color (Primary Action Buttons)'
            ]
        ],
        "Modern JavaScript Essentials" => [
            [
                "num" => 1,
                "title" => "JS Fundamentals & ES6+ Syntax",
                "video" => "https://www.youtube.com/embed/W6NZfCO5SIk",
                "duration" => "15:10",
                "desc" => "Variables let/const, arrow functions, template literals, destructuring, and spread operator.",
                "doc_title" => "📄 JS Syntax & ES6+ Reference",
                "doc_content" => '### Modern JavaScript Features
```javascript
const name = "Learnora JS";
const greet = (user) => `Hello, ${user}! Welcome to ${name}`;
const skills = ["JS", "React", "Node"];
const [primary, ...rest] = skills;
console.log(greet("Student"), primary);
```'
            ],
            [
                "num" => 2,
                "title" => "DOM Manipulation & Event Handling",
                "video" => "https://www.youtube.com/embed/y17RuWkWdn8",
                "duration" => "18:40",
                "desc" => "Select DOM elements, handle click/submit events, dynamic element creation, and class toggling.",
                "doc_title" => "📄 DOM Manipulation Cheat Sheet",
                "doc_content" => '### DOM Selection & Event Listeners
```javascript
const btn = document.querySelector("#submitBtn");
btn.addEventListener("click", (e) => {
    e.preventDefault();
    document.body.classList.toggle("dark-mode");
});
```'
            ],
            [
                "num" => 3,
                "title" => "Asynchronous JavaScript & Fetch API",
                "video" => "https://www.youtube.com/embed/V_Kr9OSfDeU",
                "duration" => "22:15",
                "desc" => "Understand Callbacks, Promises, Async/Await syntax, and making AJAX/JSON HTTP requests.",
                "doc_title" => "📄 Async JS & Promises Guide",
                "doc_content" => '### Async / Await Pattern
```javascript
async function loadData() {
    try {
        const res = await fetch("https://api.example.com/data");
        const json = await res.json();
        console.log("Loaded data:", json);
    } catch (err) {
        console.error("Fetch failed:", err);
    }
}
```'
            ]
        ],
        "React JS Frontend Development" => [
            [
                "num" => 1,
                "title" => "Introduction to React & JSX Component Architecture",
                "video" => "https://www.youtube.com/embed/SqcY0GlETPk",
                "duration" => "16:20",
                "desc" => "React virtual DOM, JSX syntax, functional components, and props passing.",
                "doc_title" => "📄 React Components & JSX Guide",
                "doc_content" => '### Functional Component Example
```jsx
function CourseCard({ title, duration }) {
    return (
        <div className="card">
            <h3>{title}</h3>
            <p>Duration: {duration}</p>
        </div>
    );
}
```'
            ],
            [
                "num" => 2,
                "title" => "State Management with useState & useEffect Hooks",
                "video" => "https://www.youtube.com/embed/35lXWvCuM8o",
                "duration" => "20:45",
                "desc" => "Manage component state, side effects, API data fetching, and lifecycle events with hooks.",
                "doc_title" => "📄 React Hooks Manual",
                "doc_content" => '### React Hooks Syntax
```jsx
import { useState, useEffect } from "react";

function Counter() {
    const [count, setCount] = useState(0);
    useEffect(() => {
        document.title = `Count: ${count}`;
    }, [count]);
    return <button onClick={() => setCount(count + 1)}>Increment ({count})</button>;
}
```'
            ],
            [
                "num" => 3,
                "title" => "React Router Navigation & Global State",
                "video" => "https://www.youtube.com/embed/Law7wfdg_fq",
                "duration" => "24:10",
                "desc" => "Single-page app routing with React Router v6, Context API, and state providers.",
                "doc_title" => "📄 React Routing & Context API Blueprint",
                "doc_content" => '### Context API & Router Setup
```jsx
import { BrowserRouter, Routes, Route, Link } from "react-router-dom";

function App() {
    return (
        <BrowserRouter>
            <nav><Link to="/">Home</Link> | <Link to="/courses">Courses</Link></nav>
            <Routes>
                <Route path="/" element={<Home />} />
                <Route path="/courses" element={<Courses />} />
            </Routes>
        </BrowserRouter>
    );
}
```'
            ]
        ],
        "PHP & Laravel Web Framework" => [
            [
                "num" => 1,
                "title" => "PHP 8 Syntax, Arrays & Functions",
                "video" => "https://www.youtube.com/embed/OK_JCtrrv-c",
                "duration" => "14:50",
                "desc" => "Modern PHP syntax, associative arrays, built-in functions, and type declarations.",
                "doc_title" => "📄 PHP 8 Reference Notes",
                "doc_content" => '### PHP 8 Typed Functions
```php
<?php
function getCourseDetails(string $title, int $lessons = 10): array {
    return [
        "title" => $title,
        "total_lessons" => $lessons,
        "status" => "active"
    ];
}
```'
            ],
            [
                "num" => 2,
                "title" => "Introduction to Laravel 10 & Blade Templating",
                "video" => "https://www.youtube.com/embed/MYyJ4Ath4B4",
                "duration" => "23:00",
                "desc" => "Artisan CLI, Laravel routing, Controllers, Blade views, and template inheritance.",
                "doc_title" => "📄 Laravel Setup & Routing Handbook",
                "doc_content" => '### Laravel Route & Controller
```php
use App\Http\Controllers\CourseController;

Route::get("/courses", [CourseController::class, "index"]);
Route::get("/courses/{id}", [CourseController::class, "show"]);
```'
            ],
            [
                "num" => 3,
                "title" => "Eloquent ORM & Database Migrations",
                "video" => "https://www.youtube.com/embed/ImtZ5yENzgE",
                "duration" => "21:40",
                "desc" => "Database schema migrations, Eloquent models, relationships, and CRUD operations.",
                "doc_title" => "📄 Eloquent ORM & Migrations Manual",
                "doc_content" => '### Eloquent Model Query
```php
$activeCourses = Course::where("status", "active")
    ->orderBy("title", "asc")
    ->get();
```'
            ]
        ],
        "Cyber Security & Ethical Hacking" => [
            [
                "num" => 1,
                "title" => "Fundamentals of Information Security & CIA Triad",
                "video" => "https://www.youtube.com/embed/inWWhr5tnEA",
                "duration" => "12:40",
                "desc" => "Confidentiality, Integrity, Availability, threat models, attack vectors, and security policies.",
                "doc_title" => "📄 Cybersecurity Fundamentals Guide",
                "doc_content" => '### CIA Triad Principles
- **Confidentiality**: Data encryption and strict access controls.
- **Integrity**: Hashing (SHA-256) ensuring data is untampered.
- **Availability**: DDoS mitigation and redundant backups.'
            ],
            [
                "num" => 2,
                "title" => "Network Reconnaissance & Port Scanning",
                "video" => "https://www.youtube.com/embed/3Kq1MIfTWCE",
                "duration" => "18:10",
                "desc" => "Information gathering, Nmap network scanning, Wireshark packet inspection, and footprinting.",
                "doc_title" => "📄 Reconnaissance & Nmap Cheat Sheet",
                "doc_content" => '### Nmap Commands
```bash
# SYN Stealth Scan with OS Detection
nmap -sS -O 192.168.1.1

# Service version detection
nmap -sV -p 80,443 target_ip
```'
            ],
            [
                "num" => 3,
                "title" => "Web Application Vulnerabilities & OWASP Top 10",
                "video" => "https://www.youtube.com/embed/2u3sZ10_6Rk",
                "duration" => "22:30",
                "desc" => "SQL Injection (SQLi), Cross-Site Scripting (XSS), CSRF, and broken authentication.",
                "doc_title" => "📄 OWASP Top 10 Vulnerabilities Handbook",
                "doc_content" => '### Preventing SQL Injection
- Always use Prepared Statements / Parameterized Queries instead of concatenating strings.
- Example: `$stmt->bind_param("s", $user_input);`'
            ]
        ],
        "Network Security & Cryptography" => [
            [
                "num" => 1,
                "title" => "Symmetric & Asymmetric Encryption Algorithms",
                "video" => "https://www.youtube.com/embed/NuyzuNBFWxQ",
                "duration" => "15:30",
                "desc" => "AES, RSA encryption, hashing algorithms (SHA-256, MD5), and digital signatures.",
                "doc_title" => "📄 Cryptography Mathematics & Standards",
                "doc_content" => '### Encryption Types
- **Symmetric**: Single shared secret key (AES-256). Fast for bulk data.
- **Asymmetric**: Public Key for encryption, Private Key for decryption (RSA 4096).'
            ],
            [
                "num" => 2,
                "title" => "SSL/TLS Protocols & Public Key Infrastructure",
                "video" => "https://www.youtube.com/embed/r1njt6p_s_g",
                "duration" => "18:20",
                "desc" => "TLS Handshake, X.509 certificates, Certificate Authorities (CA), and HTTPS security.",
                "doc_title" => "📄 SSL/TLS Security Protocols Guide",
                "doc_content" => '### TLS 1.3 Handshake Steps
1. Client Hello (Supported cipher suites & Key exchange parameters)
2. Server Hello & Digital Certificate verification
3. Session Key Generation & Encrypted communication'
            ]
        ],
        "Full-Stack React & Next.js" => [
            [
                "num" => 1,
                "title" => "Introduction to Next.js App Router & Server Components",
                "video" => "https://www.youtube.com/embed/ZVnjOPwW44A",
                "duration" => "17:40",
                "desc" => "Server-side rendering (SSR), Static Site Generation (SSG), and file-based App Router.",
                "doc_title" => "📄 Next.js Architecture Notes",
                "doc_content" => '### Server Component Syntax
```tsx
// app/courses/page.tsx
export default async function CoursesPage() {
    const courses = await fetchCoursesFromDb();
    return <div>{courses.map(c => <h2 key={c.id}>{c.title}</h2>)}</div>;
}
```'
            ],
            [
                "num" => 2,
                "title" => "API Routes, Server Actions & Database Integration",
                "video" => "https://www.youtube.com/embed/d5x00snfP8w",
                "duration" => "21:15",
                "desc" => "Build REST/GraphQL APIs, Next.js Server Actions, and connecting PostgreSQL/Prisma.",
                "doc_title" => "📄 Server Actions & DB Integration Guide",
                "doc_content" => '### Server Action Example
```tsx
"use server";

export async function enrollCourse(courseId: number) {
    await db.enrollments.create({ data: { courseId } });
}
```'
            ]
        ],
        "Cloud Computing & AWS Essentials" => [
            [
                "num" => 1,
                "title" => "Introduction to Cloud Infrastructure & AWS Core Services",
                "video" => "https://www.youtube.com/embed/ulprqHHWlng",
                "duration" => "14:30",
                "desc" => "IaaS, PaaS, SaaS, AWS Global Infrastructure, Regions, Availability Zones, and IAM.",
                "doc_title" => "📄 AWS Core Services Cheat Sheet",
                "doc_content" => '### AWS Pillars
- **EC2**: Elastic Compute Cloud (Virtual Servers)
- **S3**: Simple Storage Service (Object Storage)
- **IAM**: Identity and Access Management'
            ],
            [
                "num" => 2,
                "title" => "Serverless Architecture with AWS Lambda & DynamoDB",
                "video" => "https://www.youtube.com/embed/eOBq__hVUBE",
                "duration" => "21:00",
                "desc" => "Build event-driven microservices with Lambda functions, API Gateway, and NoSQL DynamoDB.",
                "doc_title" => "📄 Serverless AWS Guide",
                "doc_content" => '### AWS Lambda Event Handler
```javascript
exports.handler = async (event) => {
    return {
        statusCode: 200,
        body: JSON.stringify({ message: "Hello from AWS Lambda!" })
    };
};
```'
            ]
        ],
        "Data Structures & Algorithms in JS" => [
            [
                "num" => 1,
                "title" => "Big-O Time & Space Complexity Analysis",
                "video" => "https://www.youtube.com/embed/g2o22C3CRfU",
                "duration" => "13:50",
                "desc" => "Understand O(1), O(n), O(n log n), O(n^2) space-time tradeoffs in code evaluation.",
                "doc_title" => "📄 Big-O Cheat Sheet & Complexity Guide",
                "doc_content" => '### Complexity Rankings (Best to Worst)
1. O(1) Constant
2. O(log n) Logarithmic
3. O(n) Linear
4. O(n log n) Linearithmic
5. O(n^2) Quadratic'
            ],
            [
                "num" => 2,
                "title" => "Arrays, Linked Lists & Stacks/Queues in JS",
                "video" => "https://www.youtube.com/embed/RBSGKlAvoiM",
                "duration" => "19:10",
                "desc" => "Implement singly and doubly linked lists, LIFO Stacks, and FIFO Queues in JS.",
                "doc_title" => "📄 Linear Data Structures Manual",
                "doc_content" => '### Stack Implementation
```javascript
class Stack {
    constructor() { this.items = []; }
    push(item) { this.items.push(item); }
    pop() { return this.items.pop(); }
}
```'
            ]
        ]
    ];

    // Prepare insert query
    $stmt_insert = $conn->prepare(
        "INSERT INTO lessons (course_id, lesson_number, title, video_url, duration, description, document_title, document_content)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            video_url = VALUES(video_url),
            duration = VALUES(duration),
            description = VALUES(description),
            document_title = VALUES(document_title),
            document_content = VALUES(document_content)"
    );

    foreach ($all_courses as $c_id => $c_title) {
        $lessons_list = $seed_data[$c_title] ?? null;

        if (!$lessons_list) {
            $lessons_list = [
                [
                    "num" => 1,
                    "title" => "Welcome & Overview of " . $c_title,
                    "video" => "https://www.youtube.com/embed/rfscVS0vtbw",
                    "duration" => "10:00",
                    "desc" => "Introduction to the course objectives, roadmap, and learning outcomes.",
                    "doc_title" => "📄 Course Syllabus & Setup Notes",
                    "doc_content" => "### Course Syllabus\nWelcome to " . $c_title . ". Follow along with video lessons and study guides."
                ],
                [
                    "num" => 2,
                    "title" => "Core Concepts & Practical Applications",
                    "video" => "https://www.youtube.com/embed/kqtD5dpn9C8",
                    "duration" => "15:00",
                    "desc" => "In-depth walkthrough of fundamental concepts and hands-on examples.",
                    "doc_title" => "📄 Core Concepts Study Guide",
                    "doc_content" => "### Study Notes\nDetailed notes and step-by-step breakdown of key principles."
                ],
                [
                    "num" => 3,
                    "title" => "Final Assessment & Project Wrap-up",
                    "video" => "https://www.youtube.com/embed/8ext9G7xspg",
                    "duration" => "20:00",
                    "desc" => "Apply your knowledge in a practical project to earn your certificate.",
                    "doc_title" => "📄 Final Project & Certificate Guide",
                    "doc_content" => "### Final Assessment\nComplete all lessons in this course to claim your verified certificate."
                ]
            ];
        }

        foreach ($lessons_list as $l) {
            $num = (int)$l["num"];
            $title = $l["title"];
            $video = $l["video"];
            $dur = $l["duration"];
            $desc = $l["desc"];
            $doc_title = $l["doc_title"];
            $doc_content = $l["doc_content"];

            $stmt_insert->bind_param("iissssss", $c_id, $num, $title, $video, $dur, $desc, $doc_title, $doc_content);
            $stmt_insert->execute();
        }
    }

    $stmt_insert->close();
}

// Auto-run when required or loaded
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    check_and_init_db($conn);
}
