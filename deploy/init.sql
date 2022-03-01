-- ----------------------------
-- Records of scorings
-- ----------------------------
INSERT INTO `scorings` VALUES (1, '转点香主', 3, NULL, '2022-02-28 02:27:35', '2022-02-28 02:27:35');
INSERT INTO `scorings` VALUES (2, '四海BOSS', 10, NULL, '2022-02-28 02:27:35', '2022-02-28 02:27:35');
INSERT INTO `scorings` VALUES (3, '枭野决战', 10, NULL, '2022-02-28 02:27:35', '2022-02-28 02:27:35');
INSERT INTO `scorings` VALUES (4, '修罗城', 10, NULL, '2022-02-28 02:27:35', '2022-02-28 02:27:35');
INSERT INTO `scorings` VALUES (5, '世界BOSS', 10, NULL, '2022-02-28 02:27:35', '2022-02-28 02:27:35');
INSERT INTO `scorings` VALUES (6, '帮派联赛', 20, NULL, '2022-02-28 02:27:35', '2022-02-28 02:27:35');
INSERT INTO `scorings` VALUES (7, '天龙号', 5, NULL, '2022-02-28 02:27:35', '2022-02-28 02:27:35');
INSERT INTO `scorings` VALUES (8, '打架', 10, NULL, '2022-02-28 02:27:35', '2022-02-28 02:27:35');

-- ----------------------------
-- Records of admin_menu
-- ----------------------------
TRUNCATE table `admin_menu`;
INSERT INTO `admin_menu` VALUES (1, 0, 1, 'Index', 'feather icon-bar-chart-2', '/', '', 1, '2022-02-28 02:27:35', NULL);
INSERT INTO `admin_menu` VALUES (2, 0, 5, 'Admin', 'feather icon-settings', '', '', 1, '2022-02-28 02:27:35', '2022-03-01 10:41:17');
INSERT INTO `admin_menu` VALUES (3, 2, 6, 'Users', '', 'auth/users', '', 1, '2022-02-28 02:27:35', '2022-03-01 10:41:17');
INSERT INTO `admin_menu` VALUES (4, 2, 7, 'Roles', '', 'auth/roles', '', 1, '2022-02-28 02:27:35', '2022-03-01 10:41:17');
INSERT INTO `admin_menu` VALUES (5, 2, 8, 'Permission', '', 'auth/permissions', '', 1, '2022-02-28 02:27:35', '2022-03-01 10:41:17');
INSERT INTO `admin_menu` VALUES (6, 2, 9, 'Menu', '', 'auth/menu', '', 1, '2022-02-28 02:27:35', '2022-03-01 10:41:17');
INSERT INTO `admin_menu` VALUES (7, 2, 10, 'Extensions', '', 'auth/extensions', '', 1, '2022-02-28 02:27:35', '2022-03-01 10:41:17');
INSERT INTO `admin_menu` VALUES (8, 0, 2, 'Members', 'fa-address-card-o', 'members', '', 1, '2022-03-01 10:40:17', '2022-03-01 10:41:17');
INSERT INTO `admin_menu` VALUES (9, 0, 3, 'Events', 'fa-gamepad', 'events', '', 1, '2022-03-01 10:40:50', '2022-03-01 10:41:17');
INSERT INTO `admin_menu` VALUES (10, 0, 4, 'Scorings', 'fa-list-ul', 'scorings', '', 1, '2022-03-01 10:41:09', '2022-03-01 10:41:17');