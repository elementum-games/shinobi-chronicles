/*
//Required on the same page
<script src="
https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js
"></script>
*/

const MyChart = ({
  chartContainer
}) => {
  React.useEffect(() => {
    const canvasElement = document.getElementById('chart');
    const context = canvasElement.getContext('2d');
    const myChart = new Chart(context, {
      type: 'bar',
      data: {
        labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
        datasets: [{
          label: 'Dataset',
          data: [12, 19, 3, 5, 2, 3],
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1
        }]
      },
      options: {}
    });
  }, []); // Empty dependency array means this effect runs once after initial render

  return /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("div", null, chartContainer), /*#__PURE__*/React.createElement("canvas", {
    id: "chart",
    style: {
      backgroundColor: 'whitesmoke'
    }
  }));
};
export default MyChart;