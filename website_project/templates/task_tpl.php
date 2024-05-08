<div class="task" style="border-color: {BORDER_COLOR}; background-color: {BACKGROUND_COLOR};">
    <div class="task-heading">
        <h2>{TASK_TITLE}</h2>
    </div>
    <div class="task-body">
        <p class="task-time">{TASK_DATE}</p>
    </div>
    <div class="task-description">
        <p class="task-description-text">Description: {TASK_DESCRIPTION}</p>
    </div>
    <form action="folder.php" method="post">
        <input type="hidden" value={TASK_ID} name="taskid">
        <input type="hidden" value="delete" name="action">
        <input type="submit" value="COMPLETE TASK" name="submit" class="close-button">
    </form>
    <img src="img/gear.png" class="gear-icon" onclick="showSettings({TASK_ID})"></img>
    <div id="settings_{TASK_ID}" class="settings">
        <span class="close" onclick="hideSettings({TASK_ID})">&times;</span>
        <h3>Modify the task</h3>
        <form method="post" action="modifytask.php">
            <input type="hidden" name="taskId" value={TASK_ID}>
            <label for="taskTitle">Task Title</label><br>
            <input type="text" id="taskTitle" name="taskTitle" placeholder="New design" pattern="[A-Za-z0-9!@#$%^&*()_+-]+" maxlength="30"><br><br>

            <label for="taskTitle">Task Description</label><br>
            <textarea rows="5" cols="20" id="taskDescription" name="taskDescription"
                placeholder="Add new elements to design." maxlength="150"></textarea><br><br>

            <label for="status">Choose the status</label><br>
            <select id="status" name="status">
                <option value="urgent">Urgent</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select><br><br>

            <label for="taskDate">Task deadline</label><br>
            <input type="date" name="taskDate" id="taskDate"><br><br>

            <input type="submit" name="submit" id="submit" value="Modify Task" class="submit-button">
        </form>
    </div>
</div>