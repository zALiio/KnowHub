USE knowhub;
INSERT INTO categories(name,slug,description) VALUES
('Artificial Intelligence','artificial-intelligence','Explore intelligent systems, search, planning, and modern AI.'),
('Machine Learning','machine-learning','Models, training, evaluation, and practical ML questions.'),
('Data Science','data-science','Statistics, data analysis, visualization, and pipelines.'),
('Python','python','Python language questions, libraries, and best practices.'),
('Web Development','web-development','Frontend, backend, APIs, and web architecture.'),
('Database Systems','database-systems','Relational design, SQL, indexing, and data integrity.'),
('Computer Networks','computer-networks','Protocols, routing, network security, and troubleshooting.'),
('Cybersecurity','cybersecurity','Secure coding, threat models, and defensive practice.'),
('Algorithms and Data Structures','algorithms-data-structures','Problem solving, complexity, and core CS concepts.');

-- Demo password for all sample users: password (change after your walkthrough).
INSERT INTO users(full_name,username,email,password_hash,bio) VALUES
('Mariam Hassan','mariam.h','mariam@example.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Computer science student exploring machine learning and practical AI.'),
('Omar Adel','omar.codes','omar@example.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Building useful things with Python, PHP, and open data.'),
('Nour Samir','nour.data','nour@example.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Interested in data science, databases, and clear explanations.'),
('Youssef Tarek','youssef.dev','youssef@example.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Web developer learning more about systems and security.');

INSERT INTO questions(user_id,category_id,title,body,created_at) VALUES
(1,2,'How do I decide whether to standardize features before training a model?','I am comparing logistic regression with a tree-based model on a small tabular dataset. Some features have very different scales. Is standardization always helpful, and how should I decide without leaking information from the test set?',NOW()-INTERVAL 2 HOUR),
(2,5,'What is a clean way to validate a PHP form on both client and server?','I understand that browser validation improves the experience, but it can be bypassed. What checks belong in JavaScript and what must always happen again on the PHP side?',NOW()-INTERVAL 7 HOUR),
(3,6,'When should a database index be added to a foreign key column?','I have a questions table linked to users and categories. Which indexes should I add, and how can I tell whether an index is helping instead of slowing down writes?',NOW()-INTERVAL 1 DAY),
(4,9,'How can I explain Big O notation with a practical example?','I am preparing a short explanation for classmates. I want an example that makes the difference between O(n) and O(n²) intuitive without getting lost in formal notation.',NOW()-INTERVAL 2 DAY),
(1,1,'What is the difference between a search algorithm and a learning algorithm?','I see both terms in AI courses. Could someone explain how a classical search method differs from a machine learning model, and when each is a better fit?',NOW()-INTERVAL 3 DAY);

INSERT INTO tags(name,slug) VALUES ('feature-scaling','feature-scaling'),('php','php'),('validation','validation'),('sql','sql'),('indexing','indexing'),('algorithms','algorithms'),('big-o','big-o'),('artificial-intelligence','artificial-intelligence');
INSERT INTO question_tags(question_id,tag_id) VALUES (1,1),(1,8),(2,2),(2,3),(3,4),(3,5),(4,6),(4,7),(5,8),(5,6);
INSERT INTO answers(question_id,user_id,body,created_at) VALUES
(1,2,'Fit the scaler only on the training split, then use that fitted scaler to transform validation and test data. Linear models often benefit from scaling; many decision-tree models do not because their splits depend on ordering rather than distance.',NOW()-INTERVAL 1 HOUR),
(2,1,'Use JavaScript for immediate feedback, but repeat every important rule in PHP before saving. Validate lengths and formats, escape output when rendering, and use prepared statements for database writes.',NOW()-INTERVAL 3 HOUR),
(3,4,'Index columns used often in joins and filters. MySQL requires indexes on referenced keys and commonly creates them for foreign keys, but confirm with SHOW INDEX and EXPLAIN for your actual queries.',NOW()-INTERVAL 20 HOUR);
UPDATE questions SET accepted_answer_id=1 WHERE id=1;
INSERT INTO question_votes(question_id,user_id,vote_value) VALUES (1,3,1),(1,4,1),(2,3,1),(3,1,1),(4,2,1);
INSERT INTO answer_votes(answer_id,user_id,vote_value) VALUES (1,1,1),(1,3,1),(2,2,1),(3,2,1);
